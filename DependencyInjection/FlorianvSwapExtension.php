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

        if (isset($config['providers'])) {
            foreach ($config['providers'] as $providerName => $providerConfig) {
                switch ($providerName) {
                    case 'yahoo_finance':
                    case 'google_finance':
                    case 'european_central_bank':
                        $this->addProvider($container, $providerName, array(
                            new Reference('florianv_swap.client')
                        ));
                        break;

                    case 'open_exchange_rates':
                        $this->addProvider($container, $providerName, array(
                            new Reference('florianv_swap.client'),
                            $providerConfig['app_id'],
                            $providerConfig['enterprise']
                        ));
                        break;

                    case 'xignite':
                        $this->addProvider($container, $providerName, array(
                            new Reference('florianv_swap.client'),
                            $providerConfig['token'],
                        ));
                        break;
                }
            }
        }
    }

    /**
     * Creates the provider definition and add it to the container.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $arguments
     */
    private function addProvider(ContainerBuilder $container, $name, $arguments = array())
    {
        $definition = new Definition('%florianv_swap.provider.'.$name.'.class%', $arguments);
        $definition->setPublic(false);
        $definition->addTag('florianv_swap.provider');

        $container->setDefinition(sprintf('florianv_swap.provider.%s', $name), $definition);
    }
}
