<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * Class TwigPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TwigPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition('twig')
            ->addMethodCall('addGlobal', [
                'commerce_default_currency',
                new Parameter('ekyna_commerce.default.currency'),
            ])
            ->addMethodCall('addGlobal', [
                'commerce_default_country',
                new Parameter('ekyna_commerce.default.country')
            ]);
    }
}
