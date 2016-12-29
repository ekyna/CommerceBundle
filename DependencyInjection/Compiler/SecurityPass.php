<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SecurityPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('security.logout_listener')) {
            return;
        }

        // Register the logout handler
        $logoutListenerDefinition = $container->getDefinition('security.logout_listener');
        $logoutListenerDefinition->addMethodCall('addHandler', [new Reference('ekyna_commerce.security.logout_handler')]);
    }
}
