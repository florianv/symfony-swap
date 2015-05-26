<?php

/*
 * This file is part of the Swap Bundle.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Florianv\SwapBundle\Tests\DependencyInjection\Compiler;

use Florianv\SwapBundle\DependencyInjection\Compiler\ProviderPass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class ProviderPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProvidersAreAdded()
    {
        $swapDefinition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with('florianv_swap.swap')
            ->willReturn($swapDefinition)
        ;

        $containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('florianv_swap.provider')
            ->willReturn(array('foo' => array(), 'bar' => array()))
        ;

        $chainDefinition = new Definition('%florianv_swap.provider.chain.class%', array(array(
            new Reference('foo'),
            new Reference('bar')
        )));

        $swapDefinition
            ->expects($this->at(0))
            ->method('replaceArgument')
            ->with(0, $chainDefinition)
        ;

        $pass = new ProviderPass();
        $pass->process($containerBuilder);
    }

    public function testOneProvider()
    {
        $swapDefinition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with('florianv_swap.swap')
            ->willReturn($swapDefinition)
        ;

        $containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('florianv_swap.provider')
            ->willReturn(array('foo' => array()))
        ;

        $swapDefinition
            ->expects($this->at(0))
            ->method('replaceArgument')
            ->with(0, new Reference('foo'))
        ;

        $pass = new ProviderPass();
        $pass->process($containerBuilder);
    }

    public function testSortProviders()
    {
        $swapDefinition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder
            ->expects($this->once())
            ->method('getDefinition')
            ->with('florianv_swap.swap')
            ->willReturn($swapDefinition)
        ;

        $containerBuilder
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with('florianv_swap.provider')
            ->willReturn(array(
                'foo' => array(0 => array('priority' => 25)),
                'bar' => array(),
                'baz' => array(0 => array('priority' => '13'))
            ))
        ;

        $chainDefinition = new Definition('%florianv_swap.provider.chain.class%', array(array(
            new Reference('foo'),
            new Reference('baz'),
            new Reference('bar')
        )));

        $swapDefinition
            ->expects($this->at(0))
            ->method('replaceArgument')
            ->with(0, $chainDefinition)
        ;

        $pass = new ProviderPass();
        $pass->process($containerBuilder);
    }
}
