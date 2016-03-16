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
            ->validate()
                ->ifTrue(function($providers) {
                    return !isset($providers['providers']) || count($providers['providers']) === 0;
                })
                ->thenInvalid('You must define at least one provider.')
                ->end()
            ->children()
                ->scalarNode('http_adapter')->defaultValue('florianv_swap.http_adapter.file_get_contents')->end()
                ->arrayNode('cache')
                    ->children()
                        ->integerNode('ttl')->isRequired()->end()
                        ->append($this->getCacheDriverNode('doctrine'))
                    ->end()
                ->end()
                ->arrayNode('providers')
                    ->children()
                        ->append($this->createSimpleProviderNode('yahoo_finance'))
                        ->append($this->createSimpleProviderNode('google_finance'))
                        ->append($this->createSimpleProviderNode('european_central_bank'))
                        ->append($this->createSimpleProviderNode('national_bank_of_romania'))
                        ->append($this->createSimpleProviderNode('central_bank_of_republic_turkey'))
                        ->arrayNode('open_exchange_rates')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('app_id')->isRequired()->cannotBeEmpty()->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('xignite')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('token')->isRequired()->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                        ->append($this->createSimpleProviderNode('webservicex'))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function createSimpleProviderNode($name)
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root($name);

        $node
            ->children()
                ->integerNode('priority')->defaultValue(0)->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Return a cache driver node
     *
     * @param string $name
     *
     * @return ArrayNodeDefinition
     */
    private function getCacheDriverNode($name)
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root($name);

        $node
            ->addDefaultsIfNotSet()
            ->beforeNormalization()
                ->ifString()
                ->then(function($v) { return array('type' => $v); })
            ->end()
            ->isRequired()
            ->children()
                ->scalarNode('type')
                    ->info('A cache type or service id')
                    ->defaultValue('array')
                ->end()
            ->end()
        ;

        return $node;
    }
}
