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
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('florianv_swap');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('florianv_swap');
        }

        $rootNode
            ->fixXmlConfig('provider')
            ->validate()
                ->ifTrue(function($config) {
                    return !isset($config['providers']) || count($config['providers']) === 0;
                })
                ->thenInvalid('You must define at least one provider.')
            ->end()
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('ttl')
                            ->defaultValue(3600)
                        ->end()
                        ->scalarNode('type')
                            ->info('A cache type or service id')
                            ->treatFalseLike(null)
                            ->treatTrueLike(null)
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('providers')
                    ->children()
                        ->arrayNode('fixer')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('access_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->append($this->createSimpleProviderNode('cryptonator'))
                        ->arrayNode('exchange_rates_api')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('access_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->append($this->createSimpleProviderNode('webservicex'))
                        ->append($this->createSimpleProviderNode('central_bank_of_czech_republic'))
                        ->append($this->createSimpleProviderNode('central_bank_of_republic_turkey'))
                        ->append($this->createSimpleProviderNode('european_central_bank'))
                        ->append($this->createSimpleProviderNode('national_bank_of_romania'))
                        ->append($this->createSimpleProviderNode('russian_central_bank'))
                        ->arrayNode('currency_data_feed')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('api_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('currency_layer')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('access_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('forge')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('api_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('open_exchange_rates')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('app_id')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('xignite')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('token')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('xchangeapi')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('api_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('currency_converter')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->scalarNode('access_key')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->booleanNode('enterprise')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('array')
                            ->children()
                                ->integerNode('priority')->defaultValue(0)->end()
                                ->variableNode('rates')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->ifTrue(function($config) {
                                            if (!is_array($config) || empty($config)) {
                                                return true;
                                            }

                                            if (!$this->validateArrayProviderEntry($config)) {
                                                return true;
                                            }

                                            return false;
                                        })
                                        ->thenInvalid('Invalid configuration for array provider.')
                                    ->end()
                                ->end()
                                ->variableNode('historicalRates')
                                    ->treatFalseLike(null)
                                    ->treatTrueLike(null)
                                    ->cannotBeEmpty()
                                    ->validate()
                                        ->ifTrue(function($config) {
                                            if (!is_array($config) || empty($config)) {
                                                return true;
                                            }

                                            if (!$this->validateArrayProviderEntry($config)) {
                                                return true;
                                            }

                                            return false;
                                        })
                                        ->thenInvalid('Invalid configuration for array provider.')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }


    private function createSimpleProviderNode($name)
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder($name);
            $node = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $node = $treeBuilder->root($name);
        }

        $node
            ->children()
                ->integerNode('priority')->defaultValue(0)->end()
            ->end()
        ;
        return $node;
    }

    /**
     * Validates an array provider config entry.
     *
     * @param array $entry
     *
     * @return bool
     */
    private function validateArrayProviderEntry(array $entry)
    {
        foreach ($entry as $key => $value) {
            if (preg_match('~^[1|2][0-9]{3}-[0-9]{2}-[0-9]{2}$~', $key)) {
                if (is_array($value)) {
                    return $this->validateArrayProviderEntry($value);
                }
            } elseif (preg_match('~^[A-Z]+/[A-Z]+$~', $key)) {
                if (is_float($value) && 0 < $value) {
                    continue;
                }
            }

            return false;
        }

        return true;
    }
}
