<?php

declare(strict_types=1);

namespace Florianv\SwapBundle\DependencyInjection\Compiler;

use Florianv\SwapBundle\DependencyInjection\Configuration;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class AddCustomCacheServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('florianv_swap') ?? [];
        $config = (new Processor())->processConfiguration(new Configuration(), $configs);

        $type = $config['cache']['type'] ?? null;
        $ttl = $config['cache']['ttl'] ?? null;

        if ($type === null || $ttl === null || in_array($type, ['array', 'apcu', 'filesystem'], true)) {
            return;
        }

        $type = ltrim($type, '@');

        if (!$container->hasDefinition($type)) {
            throw new InvalidArgumentException("Unexpected swap cache type '$type'.");
        }

        $definition = $container->getDefinition($type);
        $id = $type;

        $class = $definition->getClass();

        if (is_subclass_of($class, \Symfony\Contracts\Cache\CacheInterface::class)) {
            $decoratedId = $id.'_swap_psr16';
            $decoratedDefinition = new Definition(Psr16Cache::class);
            $decoratedDefinition->addArgument(new Reference($id));
            $container->setDefinition($decoratedId, $decoratedDefinition);

            $id = $decoratedId;
        } elseif (!is_subclass_of($class, CacheInterface::class)) {
            throw new InvalidArgumentException("Service '$type' does not implements ".CacheInterface::class);
        }

        $definition = $container->getDefinition('florianv_swap.builder');
        $definition
            ->replaceArgument(0, ['cache_ttl' => $ttl])
            ->addMethodCall('useSimpleCache', [new Reference($id)]);
    }
}
