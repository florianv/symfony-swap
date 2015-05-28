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

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testNoProvider()
    {
        $config = $this->createProvidersConfig(array());
        $this->extension->load($config, $this->container);
    }

    public function testYahooFinanceProvider()
    {
        $config = $this->createProvidersConfig(array('yahoo_finance' => null));
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.yahoo_finance');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter')), $definition->getArguments());
    }

    public function testGoogleFinanceProvider()
    {
        $config = $this->createProvidersConfig(array('google_finance' => null));
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.google_finance');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter')), $definition->getArguments());
    }

    public function testEuropeanCentralBankProvider()
    {
        $config = $this->createProvidersConfig(array('european_central_bank' => null));
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.european_central_bank');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter')), $definition->getArguments());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testOpenExchangeRatesProviderMissingAppId()
    {
        $config = $this->createProvidersConfig(array('open_exchange_rates' => array()));
        $this->extension->load($config, $this->container);
    }

    public function testOpenExchangeRatesProviderDefault()
    {
        $config = $this->createProvidersConfig(array('open_exchange_rates' => array('app_id' => 'secret')));
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.open_exchange_rates');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter'), 'secret', false), $definition->getArguments());
    }

    public function testOpenExchangeRatesProvider()
    {
        $config = $this->createProvidersConfig(
            array('open_exchange_rates' => array('app_id' => 'secret', 'enterprise' => true))
        );
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.open_exchange_rates');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter'), 'secret', true), $definition->getArguments());
    }

    public function testWebserviceXProvider()
    {
        $config = $this->createProvidersConfig(array('webservicex' => null));
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.webservicex');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter')), $definition->getArguments());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testXigniteProviderMissingToken()
    {
        $config = $this->createProvidersConfig(array('xignite' => array()));
        $this->extension->load($config, $this->container);
    }

    public function testXigniteProvider()
    {
        $config = $this->createProvidersConfig(array('xignite' => array('token' => 'secret')));
        $this->extension->load($config, $this->container);

        $definition = $this->container->getDefinition('florianv_swap.provider.xignite');

        $this->assertFalse($definition->isPublic());
        $this->assertTrue($definition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter'), 'secret'), $definition->getArguments());
    }

    public function testMultipleProviders()
    {
        $config = $this->createProvidersConfig(array(
            'yahoo_finance' => null,
            'google_finance' => null,
            'xignite' => array('token' => 'secret')
        ));
        $this->extension->load($config, $this->container);

        $yahooDefinition = $this->container->getDefinition('florianv_swap.provider.yahoo_finance');
        $googleDefinition = $this->container->getDefinition('florianv_swap.provider.google_finance');
        $xigniteDefinition = $this->container->getDefinition('florianv_swap.provider.xignite');

        $this->assertFalse($yahooDefinition->isPublic());
        $this->assertTrue($yahooDefinition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter')), $yahooDefinition->getArguments());

        $this->assertFalse($googleDefinition->isPublic());
        $this->assertTrue($googleDefinition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter')), $googleDefinition->getArguments());

        $this->assertFalse($xigniteDefinition->isPublic());
        $this->assertTrue($xigniteDefinition->hasTag('florianv_swap.provider'));
        $this->assertEquals(array(new Reference('florianv_swap.http_adapter'), 'secret'), $xigniteDefinition->getArguments());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testCacheMissTtl()
    {
        $config = array(
            'florianv_swap' => array(
                'cache'     => null,
                'providers' => array('yahoo_finance' => null)
            )
        );
        $this->extension->load($config, $this->container);
    }

    public function testCache()
    {
        $config = array(
            'florianv_swap' => array(
                'cache'     => array(
                    'ttl' => 3600,
                    'doctrine' => 'apc',
                ),
                'providers' => array('yahoo_finance' => null)
            )
        );
        $this->extension->load($config, $this->container);

        $swap      = $this->container->getDefinition('florianv_swap.swap');
        $arguments = $swap->getArguments();
        $cache     = $arguments[1];

        $this->assertEquals($cache->getClass(), '%florianv_swap.cache.doctrine.class%');
        $this->assertFalse($cache->isPublic());

        $apcDefinition = new Definition('%florianv_swap.cache.doctrine.apc.class%');
        $apcDefinition->setPublic(false);
        $this->assertEquals(array($apcDefinition, 3600), $cache->getArguments());
    }

    private function createProvidersConfig(array $providers)
    {
        return array('florianv_swap' => array('providers' => $providers));
    }
}
