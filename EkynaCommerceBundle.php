<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle;

use Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler as BundlePass;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection as ComponentPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaCommerceBundle
 * @package Ekyna\Bundle\CommerceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaCommerceBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ComponentPass\ConfigureValidatorPass());
        $container->addCompilerPass(new ComponentPass\SubjectProviderPass());
        $container->addCompilerPass(new ComponentPass\RegisterViewTypePass());
        $container->addCompilerPass(new ComponentPass\PricingApiPass());
        // Before \Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass
        $container->addCompilerPass(new ComponentPass\PayumBuilderPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->addCompilerPass(new ComponentPass\ShipmentGatewayRegistryPass());
        $container->addCompilerPass(new ComponentPass\ReportRegistryPass());
        $container->addCompilerPass(new ComponentPass\NewsletterRegistriesPass());
        $container->addCompilerPass(new ComponentPass\TwigPathCompilerPass());

        $container->addCompilerPass(new BundlePass\ActionAutoConfigurePass());
        $container->addCompilerPass(new BundlePass\AccountPass());
        $container->addCompilerPass(new BundlePass\AdminMenuPass());
        // Before resource's \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass
        $container->addCompilerPass(new BundlePass\GeocodingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        // Before \Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildConfigsPass
        $container->addCompilerPass(new BundlePass\PayumPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->addCompilerPass(new BundlePass\SwapPass());
        $container->addCompilerPass(new BundlePass\TwigPass());
    }
}
