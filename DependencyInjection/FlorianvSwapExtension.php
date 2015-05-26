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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bridge\Doctrine\DependencyInjection\AbstractDoctrineExtension;

/**
 * The container extension.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class FlorianvSwapExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $config);

        $container->setAlias('florianv_swap.http_adapter', $config['http_adapter']);

        $this->loadProviders($config['providers'], $container);

        if (isset($config['cache'])) {
            $this->loadCache($config['cache'], $container);
        }
    }

    private function loadProviders(array $config, ContainerBuilder $container)
    {
        foreach ($config as $providerName => $providerConfig) {
            switch ($providerName) {
                case 'yahoo_finance':
                case 'google_finance':
                case 'european_central_bank':
                case 'national_bank_of_romania':
                case 'webservicex':
                    $this->addProvider($container, $providerName, array(
                        new Reference('florianv_swap.http_adapter'),
                    ), $providerConfig['priority']);
                    break;

                case 'open_exchange_rates':
                    $this->addProvider($container, $providerName, array(
                        new Reference('florianv_swap.http_adapter'),
                        $providerConfig['app_id'],
                        $providerConfig['enterprise']
                    ), $providerConfig['priority']);
                    break;

                case 'xignite':
                    $this->addProvider($container, $providerName, array(
                        new Reference('florianv_swap.http_adapter'),
                        $providerConfig['token'],
                    ), $providerConfig['priority']);
                    break;
            }
        }
    }

    private function loadCache(array $config, ContainerBuilder $container)
    {
        $cacheProvider = new Definition('%florianv_swap.cache.doctrine.'.$config['doctrine']['type'].'.class%');
        $cacheProvider->setPublic(false);

        $cacheDefinition = new Definition('%florianv_swap.cache.doctrine.class%', array(
            $cacheProvider,
            $config['ttl']
        ));
        $cacheDefinition->setPublic(false);

        $container->getDefinition('florianv_swap.swap')->replaceArgument(1, $cacheDefinition);
    }

    /**
     * Creates the provider definition and add it to the container.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $arguments
     */
    private function addProvider(ContainerBuilder $container, $name, array $arguments = array(), $priority = null)
    {
        $definition = new Definition('%florianv_swap.provider.'.$name.'.class%', $arguments);
        $definition->setPublic(false);
        $definition->addTag('florianv_swap.provider', array('priority' => $priority));

        $container->setDefinition(sprintf('florianv_swap.provider.%s', $name), $definition);
    }
}
