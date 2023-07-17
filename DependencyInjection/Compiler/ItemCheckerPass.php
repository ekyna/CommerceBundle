<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ItemCheckerPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ItemCheckerPass implements CompilerPassInterface
{
    public const TAG = 'ekyna_commerce.item_checker';

    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('ekyna_commerce.checker.sale_items');

        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            // Register the provider
            $definition->addMethodCall('registerChecker', [new Reference($id)]);
        }
    }
}
