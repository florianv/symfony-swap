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

use Florianv\SwapBundle\DependencyInjection\FlorianvSwapExtension;
use PHPUnit\Framework\TestCase;
use Swap\Builder;
use Swap\Swap;
use Symfony\Component\Cache\Adapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FlorianvSwapExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var FlorianvSwapExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new FlorianvSwapExtension();
    }

    /**
     * @throws \Exception
     */
    public function testBuilderService(): void
    {
        $this->buildContainer();

        /** @var Builder $builder */
        $builder = $this->container->get('florianv_swap.builder');

        self::assertInstanceOf(Builder::class, $builder);
    }

    /**
     * @throws \Exception
     */
    public function testSwapService(): void
    {
        $this->buildContainer();

        /** @var Swap $swap */
        $swap = $this->container->get('florianv_swap.swap');
        self::assertInstanceOf(Swap::class, $swap);
    }

    public function testNoProvider(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->buildContainer([], []);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testFixerProvider(): void
    {
        $this->buildContainer(['fixer' => ['access_key' => 'test']]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testForgeProvider(): void
    {
        $this->buildContainer(['forge' => ['api_key' => 'test']]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testXchangeApiProvider(): void
    {
        $this->buildContainer(['xchangeapi' => ['api-key' => 'test']]);
    }

    public function testProviderPriorities(): void
    {
        $this->buildContainer([
            'fixer' => ['access_key' => 'YOUR_KEY'],
            'european_central_bank' => [
                'priority' => 3,
            ],
            'forge' => [
                'api_key' => 'test',
                'priority' => 2,
            ]
        ]);

        $swap = $this->container->getDefinition('florianv_swap.builder');
        $calls = $swap->getMethodCalls();

        // European Central Bank first
        self::assertEquals('add', $calls[0][0]);
        self::assertEquals('european_central_bank', $calls[0][1][0]);
        self::assertEquals([], $calls[0][1][1]);

        // Forge second
        self::assertEquals('add', $calls[1][0]);
        self::assertEquals('forge', $calls[1][1][0]);
        self::assertEquals(['api_key' => 'test'], $calls[1][1][1]);

        // Fixer third
        self::assertEquals('add', $calls[2][0]);
        self::assertEquals('fixer', $calls[2][1][0]);
        self::assertEquals(['access_key' => 'YOUR_KEY', 'enterprise' => false], $calls[2][1][1]);
    }

    public function testCacheMissTtl(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['ttl' => null]);
    }

    /**
     * @throws \Exception
     */
    public function testArrayCache(): void
    {
        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['type' => 'array', 'ttl' => 60]);

        $this->assertCache(Adapter\ArrayAdapter::class, [60]);
    }

    /**
     * @throws \Exception
     */
    public function testApcuCache(): void
    {
        if (!ApcuAdapter::isSupported()) {
            self::markTestSkipped('APCU is not enabled');
        }

        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['type' => 'apcu']);

        $this->assertCache(Adapter\ApcuAdapter::class, ['swap', 3600]);
    }

    /**
     * @throws \Exception
     */
    public function testFilesystemCache(): void
    {
        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['type' => 'filesystem']);

        $this->assertCache(Adapter\FilesystemAdapter::class, ['swap', 3600]);
    }

    /**
     * Builds the container.
     *
     * @param array $providers
     * @param array $cache
     */
    private function buildContainer(array $providers = ['fixer' => ['access_key' => 'test']], array $cache = []): void
    {
        $this->extension->load([
            'florianv_swap' => [
                'providers' => $providers,
                'cache'     => $cache,
            ],
        ], $this->container);
    }

    /**
     * Makes cache assertions.
     *
     * @param $class
     * @param $config
     * @throws \Exception
     */
    private function assertCache($class, $config): void
    {
        $swap = $this->container->getDefinition('florianv_swap.builder');
        $calls = $swap->getMethodCalls();
        self::assertEquals('useSimpleCache', $calls[0][0]);

        /** @var Reference $cacheReference */
        $cacheReference = $calls[0][1][0];
        self::assertEquals('florianv_swap.cache', (string)$cacheReference);

        /** @var Definition */
        $cacheDefinition = $this->container->getDefinition('florianv_swap.cache');
        self::assertEquals(Psr16Cache::class, $cacheDefinition->getClass());
        self::assertEquals($class, $cacheDefinition->getArgument(0)->getClass());
        self::assertFalse($cacheDefinition->isPublic());

        self::assertEquals($config, $cacheDefinition->getArgument(0)->getArguments());

        $cache = $this->container->get('florianv_swap.cache');
        self::assertInstanceOf(Psr16Cache::class, $cache);
    }
}
