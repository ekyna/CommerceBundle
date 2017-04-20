<?php

declare(strict_types=1);

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
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition('payum.builder')
            ->removeMethodCall('setGenericTokenFactoryPaths')
            ->addMethodCall('setGenericTokenFactoryPaths', [
                [
                    'capture'   => 'ekyna_commerce_payment_capture',
                    'authorize' => 'ekyna_commerce_payment_authorize',
                    'payout'    => 'ekyna_commerce_payment_payout',
                    'notify'    => 'ekyna_commerce_payment_notify',
                    'cancel'    => 'ekyna_commerce_payment_cancel',
                    'refund'    => 'ekyna_commerce_payment_refund',
                    'sync'      => 'ekyna_commerce_payment_sync',
                    'accept'    => 'ekyna_commerce_payment_accept',
                    'hang'      => 'ekyna_commerce_payment_hang',
                ],
            ]);
    }
}
