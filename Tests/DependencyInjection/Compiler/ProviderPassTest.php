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

class ProviderPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProvidersAreAdded()
    {
        $swapDefinition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $containerBuilder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilder
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('florianv_swap.swap')
            ->will($this->returnValue(true))
        ;

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

        $swapDefinition
            ->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addProvider', array(new Reference('foo')))
        ;

        $swapDefinition
            ->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addProvider', array(new Reference('bar')))
        ;

        $pass = new ProviderPass();
        $pass->process($containerBuilder);
    }
}
