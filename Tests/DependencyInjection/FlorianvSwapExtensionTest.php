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
use Swap\Builder;
use Swap\Swap;
use Symfony\Component\Cache\Adapter;
use Symfony\Component\Cache\Traits\ApcuTrait;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class FlorianvSwapExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var FlorianvSwapExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new FlorianvSwapExtension();
    }

    public function testBuilderService()
    {
        $this->buildContainer();

        /** @var \Swap\Builder $builder */
        $builder = $this->container->get('florianv_swap.builder');

        $this->assertInstanceOf(Builder::class, $builder);
    }

    public function testSwapService()
    {
        $this->buildContainer();

        /** @var \Swap\Swap $swap */
        $swap = $this->container->get('florianv_swap.swap');
        $this->assertInstanceOf(Swap::class, $swap);
    }

    public function testNoProvider()
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->buildContainer([], []);
    }

    public function testFixerProvider()
    {
        $this->buildContainer(['fixer' => ['access_key' => 'test']]);
    }

    public function testForgeProvider()
    {
        $this->buildContainer(['forge' => ['api_key' => 'test']]);
    }

    public function testProviderPriorities()
    {
        $this->buildContainer([
            'fixer' => ['access_key' => 'YOUR_KEY'],
            'google' => [
                'priority' => 3,
            ],
            'forge' => [
                'api_key' => 'test',
                'priority' => 2,
            ]
        ]);

        $swap = $this->container->getDefinition('florianv_swap.builder');
        $calls = $swap->getMethodCalls();

        // Google first
        $this->assertEquals($calls[0][0], 'add');
        $this->assertEquals($calls[0][1][0], 'google');
        $this->assertEquals($calls[0][1][1], []);

        // Forge second
        $this->assertEquals($calls[1][0], 'add');
        $this->assertEquals($calls[1][1][0], 'forge');
        $this->assertEquals($calls[1][1][1], ['api_key' => 'test']);

        // Fixer third
        $this->assertEquals($calls[2][0], 'add');
        $this->assertEquals($calls[2][1][0], 'fixer');
        $this->assertEquals($calls[2][1][1], ['access_key' => 'YOUR_KEY']);
    }

    public function testCacheMissTtl()
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['ttl' => null]);
    }

    public function testArrayCache()
    {
        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['type' => 'array', 'ttl' => 60]);

        $this->assertCache(Adapter\ArrayAdapter::class, [60]);
    }

    public function testApcuCache()
    {
        if (!ApcuTrait::isSupported()) {
            $this->markTestSkipped('APCU is not enabled');
        }

        $this->buildContainer(['fixer' => ['access_key' => 'YOUR_KEY']], ['type' => 'apcu']);

        $this->assertCache(Adapter\ApcuAdapter::class, ['swap', 3600]);
    }

    public function testFilesystemCache()
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
    private function buildContainer(array $providers = ['fixer' => ['access_key' => 'test']], array $cache = [])
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
     */
    private function assertCache($class, $config)
    {
        $swap = $this->container->getDefinition('florianv_swap.builder');
        $calls = $swap->getMethodCalls();
        $this->assertEquals($calls[0][0], 'useCacheItemPool');
        /** @var Reference $cacheReference */
        $cacheReference = $calls[0][1][0];
        $this->assertEquals('florianv_swap.cache', (string)$cacheReference);

        /** @var Definition */
        $cacheDefinition = $this->container->getDefinition('florianv_swap.cache');
        $this->assertEquals($cacheDefinition->getClass(), $class);
        $this->assertFalse($cacheDefinition->isPublic());

        $this->assertEquals($config, $cacheDefinition->getArguments());

        $cache = $this->container->get('florianv_swap.cache');
        $this->assertInstanceOf($class, $cache);
    }
}
