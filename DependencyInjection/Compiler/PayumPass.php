<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PayumPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PayumPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('payum.builder')) {
            return;
        }

        $definition = $container->getDefinition('payum.builder');

        $definition->removeMethodCall('setGenericTokenFactoryPaths');
        $definition->addMethodCall('setGenericTokenFactoryPaths', [[
            'capture'   => 'ekyna_commerce_payment_capture',
            'authorize' => 'ekyna_commerce_payment_authorize',
            'payout'    => 'ekyna_commerce_payment_payout',
            'notify'    => 'ekyna_commerce_payment_notify',
            'cancel'    => 'ekyna_commerce_payment_cancel',
            'refund'    => 'ekyna_commerce_payment_refund',
            'sync'      => 'ekyna_commerce_payment_sync',
            'accept'    => 'ekyna_commerce_payment_accept',
            'hang'      => 'ekyna_commerce_payment_hang',
        ]]);
    }
}
