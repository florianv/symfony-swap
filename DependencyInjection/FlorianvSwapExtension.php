<?php

/*
 * This file is part of the Swap Bundle.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Florianv\SwapBundle\DependencyInjection;

use Psr\SimpleCache\CacheInterface;
use Swap;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The container extension.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class FlorianvSwapExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $config);

        $this->configureBuilderService($container);
        $this->configureSwapService($container);
        $this->configureCacheService($container, $config['cache']);
        $this->configureProviders($container, $config['providers']);
    }

    /**
     * Configures the builder service.
     *
     * @param ContainerBuilder $container
     */
    private function configureBuilderService(ContainerBuilder $container)
    {
        $definition = new Definition(Swap\Builder::class);
        $definition->addArgument([]);

        $container->setDefinition('florianv_swap.builder', $definition);
    }

    /**
     * Configures the swap service.
     *
     * @param ContainerBuilder $container
     */
    private function configureSwapService(ContainerBuilder $container)
    {
        $definition = new Definition(Swap\Swap::class);
        $definition->setFactory([new Reference('florianv_swap.builder'), 'build']);

        $container->setDefinition('florianv_swap.swap', $definition);
    }

    /**
     * Configures the cache service.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function configureCacheService(ContainerBuilder $container, array $config)
    {
        if (empty($type = $config['type'])) {
            return;
        }

        $ttl = $config['ttl'];
        $id = 'florianv_swap.cache';

        if (in_array($type, ['array', 'apcu', 'filesystem'], true)) {
            switch ($type) {
                case 'array':
                    $class = 'Symfony\Component\Cache\Adapter\ArrayAdapter';
                    $arguments = [$ttl];
                    break;
                case 'apcu':
                    $class = 'Symfony\Component\Cache\Adapter\ApcuAdapter';
                    $arguments = ['swap', $ttl];
                    break;
                case 'filesystem':
                    $class = 'Symfony\Component\Cache\Adapter\FilesystemAdapter';
                    $arguments = ['swap', $ttl];
                    break;
                default:
                    throw new InvalidArgumentException("Unexpected swap cache type '$type'.");
            }

            if (!class_exists($class)) {
                throw new InvalidArgumentException("Cache class $class does not exist.");
            }

            $definition = new Definition('Symfony\Component\Cache\Psr16Cache', [new Definition($class, $arguments)]);
            $definition->setPublic(false);
            $container->setDefinition($id, $definition);
        } elseif ($container->hasDefinition($type)) {
            $definition = $container->getDefinition($type);
            if (!is_subclass_of($definition->getClass(), CacheInterface::class)) {
                throw new InvalidArgumentException("Service '$type' does not implements " . CacheInterface::class);
            }

            $id = $type;
        } else {
            throw new InvalidArgumentException("Unexpected swap cache type '$type'.");
        }

        $definition = $container->getDefinition('florianv_swap.builder');
        $definition
            ->replaceArgument(0, ['cache_ttl' => $ttl])
            ->addMethodCall('useSimpleCache', [new Reference($id)]);
    }

    /**
     * Configures the providers.
     *
     * @param ContainerBuilder $container
     * @param array            $providers
     */
    public function configureProviders(ContainerBuilder $container, array $providers)
    {
        $definition = $container->getDefinition('florianv_swap.builder');

        uasort($providers, function($a , $b) {
            return $b['priority'] - $a['priority'];
        });

        foreach ($providers as $name => $config) {
            unset($config['priority']);
            $definition->addMethodCall('add', [$name, $config]);
        }
    }
}
