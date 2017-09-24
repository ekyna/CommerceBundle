<?php

namespace Ekyna\Bundle\CommerceBundle;

use Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler as BundlePass;
use Ekyna\Bundle\ResourceBundle\AbstractBundle;
use Ekyna\Component\Commerce;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\Compiler as ComponentPass;
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

        $container->addCompilerPass(new ComponentPass\ConfigureValidatorPass());
        $container->addCompilerPass(new ComponentPass\SubjectProviderPass());
        $container->addCompilerPass(new ComponentPass\RegisterViewTypePass());
        $container->addCompilerPass(new ComponentPass\PricingApiPass());
        $container->addCompilerPass(new ComponentPass\ShipmentGatewayRegistryPass());

        $container->addCompilerPass(new BundlePass\AdminMenuPass());
        $container->addCompilerPass(new BundlePass\SecurityPass());
        $container->addCompilerPass(new BundlePass\RegisterGatewayPass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelInterfaces()
    {
        return array_replace(DoctrineBundleMapping::getDefaultImplementations(), [
            Commerce\Cart\Model\CartInterface::class                 => 'ekyna_commerce.cart.class',
            Commerce\Cart\Model\CartAddressInterface::class          => 'ekyna_commerce.cart_address.class',

            Commerce\Customer\Model\CustomerInterface::class         => 'ekyna_commerce.customer.class',
            Commerce\Customer\Model\CustomerGroupInterface::class    => 'ekyna_commerce.customer_group.class',
            Commerce\Customer\Model\CustomerAddressInterface::class  => 'ekyna_commerce.customer_address.class',

            Commerce\Order\Model\OrderInterface::class               => 'ekyna_commerce.order.class',
            Commerce\Order\Model\OrderAddressInterface::class        => 'ekyna_commerce.order_address.class',

            Commerce\Payment\Model\PaymentMethodInterface::class     => 'ekyna_commerce.payment_method.class',
            Commerce\Payment\Model\PaymentTermInterface::class       => 'ekyna_commerce.payment_term.class',

            Commerce\Quote\Model\QuoteInterface::class               => 'ekyna_commerce.quote.class',
            Commerce\Quote\Model\QuoteAddressInterface::class        => 'ekyna_commerce.quote_address.class',

            Commerce\Shipment\Model\ShipmentMethodInterface::class   => 'ekyna_commerce.shipment_method.class',

            Commerce\Supplier\Model\SupplierInterface::class         => 'ekyna_commerce.supplier.class',
            Commerce\Supplier\Model\SupplierDeliveryInterface::class => 'ekyna_commerce.supplier_delivery.class',
            Commerce\Supplier\Model\SupplierOrderInterface::class    => 'ekyna_commerce.supplier_order.class',
            Commerce\Supplier\Model\SupplierProductInterface::class  => 'ekyna_commerce.supplier_product.class',
        ]);
    }
}
