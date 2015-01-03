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
        if (!$container->hasDefinition('florianv_swap.swap')) {
            return;
        }

        $definition = $container->getDefinition('florianv_swap.swap');

        foreach ($container->findTaggedServiceIds('florianv_swap.provider') as $id => $attributes) {
            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }
    }
}
