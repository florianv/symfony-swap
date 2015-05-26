<?php

/*
 * This file is part of the Swap Bundle.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Florianv\SwapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds providers tagged with "florianv_swap.provider" to the Swap service definition.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class ProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $providers = array();

        foreach ($container->findTaggedServiceIds('florianv_swap.provider') as $id => $attributes) {
            $providers[$id] = isset($attributes[0]) ? $attributes[0] : 0;
        }

        $providers = $this->normalizeProviders($providers);

        if (1 === count($providers)) {
            $provider = current($providers);
        } else {
            $provider = new Definition('%florianv_swap.provider.chain.class%', array($providers));
        }

        $definition = $container->getDefinition('florianv_swap.swap');
        $definition->replaceArgument(0, $provider);
    }

    private function normalizeProviders(array $providers)
    {
        $providersByPriority = array();

        foreach ($providers as $id => $provider) {
            $priority = isset($provider['priority']) ? $provider['priority'] : 0;
            $providersByPriority[$priority][] = new Reference($id);
        }

        // cannot use uasort because of https://github.com/florianv/FlorianvSwapBundle/pull/2#issuecomment-105971854
        krsort($providersByPriority);

        $result = array();

        foreach ($providersByPriority as $priority => $providers) {
            foreach ($providers as $provider) {
                $result[] = $provider;
            }
        }

        return $result;
    }
}
