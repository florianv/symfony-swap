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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The configuration.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('florianv_swap');

        $rootNode
            ->children()
                ->arrayNode('providers')
                    ->children()
                        ->scalarNode('yahoo_finance')->end()
                        ->scalarNode('google_finance')->end()
                        ->scalarNode('european_central_bank')->end()
                        ->arrayNode('open_exchange_rates')
                            ->children()
                                ->scalarNode('app_id')->isRequired()->cannotBeEmpty()->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('xignite')
                            ->children()
                                ->scalarNode('token')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
