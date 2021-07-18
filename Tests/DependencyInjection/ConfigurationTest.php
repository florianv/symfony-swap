<?php

/*
 * This file is part of the Swap Bundle.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Florianv\SwapBundle\Tests\DependencyInjection;

use Florianv\SwapBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class ConfigurationTest
 * @package Tests\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationTest extends TestCase
{
    /**
     * @var ?Configuration
     */
    private $configuration;

    /**
     * @var ?Processor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    protected function tearDown(): void
    {
        $this->configuration = null;
        $this->processor = null;
    }

    /**
     * @param array $providers
     *
     * @dataProvider provideValidProvidersConfigs
     */
    public function testValidProvidersConfig(array $providers): void
    {
        $configuration = $this->processor->processConfiguration($this->configuration, [
            'florianv_swap' => [
                'providers' => $providers,
            ],
        ]);

        self::assertTrue(is_array($configuration));

    }

    /**
     * @param array $providers
     *
     * @dataProvider provideInvalidProvidersConfigs
     */
    public function testInvalidProvidersConfig(array $providers): void
    {
        $this->expectException(Exception::class);

        $this->processor->processConfiguration($this->configuration, [
            'florianv_swap' => [
                'providers' => $providers,
            ],
        ]);
    }

    /**
     * @param array $cache
     *
     * @dataProvider provideValidCacheConfigs
     */
    public function testValidCacheConfig(array $cache): void
    {
        $configuration = $this->processor->processConfiguration($this->configuration, [
            'florianv_swap' => [
                'providers' => ['fixer' => ['access_key' => 'YOUR_KEY']],
                'cache'     => $cache,
            ],
        ]);

        self::assertTrue(is_array($configuration));
    }

    /**
     * @param array $cache
     *
     * @dataProvider provideInvalidCacheConfigs
     */
    public function testInvalidCacheConfig(array $cache): void
    {
        $this->expectException(Exception::class);

        $this->processor->processConfiguration($this->configuration, [
            'florianv_swap' => [
                'providers' => ['fixer' => ['access_key' => 'YOUR_KEY']],
                'cache'     => $cache,
            ],
        ]);
    }

    public function provideValidProvidersConfigs(): array
    {
        return [
            [['central_bank_of_czech_republic' => null]],
            [['central_bank_of_republic_turkey' => []]],
            [['european_central_bank' => null]],
            [['fixer' => ['access_key' => 'YOUR_KEY']]],
            [['national_bank_of_romania' => null]],
            [['webservicex' => null]],
            [['russian_central_bank' => null]],
            [['cryptonator' => null]],
            [['currency_data_feed' => ['api_key' => 'any']]],
            [['currency_layer' => ['access_key' => 'any', 'enterprise' => true]]],
            [['exchange_rates_api' => ['access_key' => 'any', 'enterprise' => false]]],
            [['forge' => ['api_key' => 'any']]],
            [['open_exchange_rates' => ['app_id' => 'any']]],
            [['xignite' => ['token' => 'any']]],
            [['xignite' => ['token' => 'any'], 'currency_layer' => ['access_key' => 'any']]],
            [['currency_converter' => ['access_key' => 'any']]],
            [['xchangeapi' => ['api-key' => 'any']]],
            [[
                'array' => [
                    'rates' => [
                        'EUR/USD' => 1.1,
                        'EUR/GBP' => 1.5,
                    ],
                    'historicalRates' => [
                        '2017-01-01' => [
                            'EUR/USD' => 1.5,
                        ],
                        '2017-01-03' => [
                            'EUR/GBP' => 1.3,
                        ],
                    ],
                ],
            ]],
            [[
                'array' => [
                    'rates' => [
                        'EUR/USD' => 1.1,
                        'EUR/GBP' => 1.5,
                    ],
                ],
            ]],
        ];
    }

    public function provideInvalidProvidersConfigs(): array
    {
        return [
            [[]],
            [['noop' => null]],
            [['central_bank_of_czech_republic' => ['any' => 'any']]],
            [['central_bank_of_republic_turkey' => ['any' => 'any']]],
            [['european_central_bank' => ['any' => 'any']]],
            [['fixer' => ['any' => 'any']]],
            [['national_bank_of_romania' => ['any' => 'any']]],
            [['webservicex' => ['any' => 'any']]],
            [['russian_central_bank' => ['any' => 'any']]],
            [['cryptonator' => ['any' => 'any']]],
            [['currency_data_feed' => ['api_key' => null]]],
            [['currency_layer' => null]],
            [['exchange_rates_api' => ['api_key' => null]]],
            [['forge' => []]],
            [['open_exchange_rates' => ['app_id' => true]]],
            [['xignite' => ['token' => []]]],
            [['currency_converter' => ['access_key' => null]]],
            [['xchangeapi' => ['api_key' => null]]],
            [['array' => null]],
            [['array' => []]],
            [['array' => ['EUR/GBP' => 1.5]]],
            [['array' => ['rates' => [['EUR/GBP' => 0]]]]],
            [['array' => ['rates' => [['any' => 'any']]]]],
            [['array' => ['rates' => [['2017-01-01' => 'any']]]]],
            [['array' => ['rates' => [['2017-01-01' => ['any' => 'any']]]]]],
        ];
    }

    public function provideValidCacheConfigs(): array
    {
        return [
            [[]],
            [['ttl' => 60, 'type' => 'array']],
        ];
    }

    public function provideInvalidCacheConfigs(): array
    {
        return [
            [['any' => 'any']],
            [['ttl' => false]],
            [['type' => []]],
        ];
    }
}
