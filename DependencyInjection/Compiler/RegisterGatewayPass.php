<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\Constants as Credit;
use Ekyna\Component\Commerce\Bridge\Payum\CreditBalance\CreditGatewayFactory;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\Constants as Outstanding;
use Ekyna\Component\Commerce\Bridge\Payum\OutstandingBalance\OutstandingGatewayFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class RegisterFactoryClass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegisterGatewayPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('payum.builder')) {
            return;
        }

        $defaultConfig = [];

        $payumBuilder = $container->getDefinition('payum.builder');

        $payumBuilder->addMethodCall('addGatewayFactoryConfig', [Outstanding::FACTORY_NAME, $defaultConfig]);
        $payumBuilder->addMethodCall('addGatewayFactory', [Outstanding::FACTORY_NAME, [OutstandingGatewayFactory::class, 'build']]);

        $payumBuilder->addMethodCall('addGatewayFactoryConfig', [Credit::FACTORY_NAME, $defaultConfig]);
        $payumBuilder->addMethodCall('addGatewayFactory', [Credit::FACTORY_NAME, [CreditGatewayFactory::class, 'build']]);
    }
}
