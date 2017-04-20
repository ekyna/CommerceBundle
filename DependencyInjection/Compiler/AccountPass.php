<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ProfileController;
use Ekyna\Bundle\CommerceBundle\Controller\Account\RegistrationController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AccountPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AccountPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_user.controller.account.profile')
            ->setClass(ProfileController::class)
            ->addMethodCall('setCustomerProvider', [new Reference('ekyna_commerce.provider.customer')])
            ->addMethodCall('setCustomerManager', [new Reference('ekyna_commerce.manager.customer')]);

        $container
            ->getDefinition('ekyna_user.controller.account.registration')
            ->setClass(RegistrationController::class)
            ->addMethodCall('setCustomerProvider', [new Reference('ekyna_commerce.provider.customer')])
            ->addMethodCall('setCustomerRepository', [new Reference('ekyna_commerce.repository.customer')])
            ->addMethodCall('setCustomerFactory', [new Reference('ekyna_commerce.factory.customer')])
            ->addMethodCall('setCustomerManager', [new Reference('ekyna_commerce.manager.customer')]);
    }
}
