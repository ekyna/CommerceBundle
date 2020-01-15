<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SwapPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SwapPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('florianv_swap.builder')) {
            return;
        }

        $definition = $container->getDefinition('florianv_swap.builder');
        $definition
            ->replaceArgument(0, ['cache_ttl' => 3600 * 24])
            ->addMethodCall('useCacheItemPool', [new Reference('ekyna_commerce.cache')]);
    }
}
