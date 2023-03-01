<?php

/*
 * This file is part of the Swap Bundle.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Florianv\SwapBundle;

use Florianv\SwapBundle\DependencyInjection\Compiler\AddCustomCacheServicePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The bundle.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class FlorianvSwapBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new AddCustomCacheServicePass(), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
