<?php

namespace Ekyna\Bundle\CommerceBundle;

use Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;
use Ekyna\Bundle\CoreBundle\AbstractBundle;
use Ekyna\Component\Commerce;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler\ConfigureValidatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaCommerceBundle
 * @package Ekyna\Bundle\CommerceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaCommerceBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigureValidatorPass());
        $container->addCompilerPass(new Compiler\SubjectProviderPass());
        $container->addCompilerPass(new Compiler\SubjectResolverPass());
        $container->addCompilerPass(new Compiler\AdminMenuPass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelInterfaces()
    {
        return array_replace(DoctrineBundleMapping::getDefaultImplementations(), [
            Commerce\Customer\Model\CustomerInterface::class        => 'ekyna_commerce.customer.class',
            Commerce\Customer\Model\CustomerAddressInterface::class => 'ekyna_commerce.customer_address.class',

            Commerce\Product\Model\ProductInterface::class          => 'ekyna_commerce.product.class',

            Commerce\Order\Model\OrderInterface::class              => 'ekyna_commerce.order.class',
            Commerce\Order\Model\OrderAddressInterface::class       => 'ekyna_commerce.order_address.class',

            Commerce\Payment\Model\PaymentInterface::class          => 'ekyna_commerce.payment.class',
            Commerce\Payment\Model\PaymentMethodInterface::class    => 'ekyna_commerce.payment_method.class',

            Commerce\Shipment\Model\ShipmentInterface::class        => 'ekyna_commerce.shipment.class',
            Commerce\Shipment\Model\ShipmentItemInterface::class    => 'ekyna_commerce.shipment_item.class',
            Commerce\Shipment\Model\ShipmentMethodInterface::class  => 'ekyna_commerce.shipment_method.class',
        ]);
    }
}
