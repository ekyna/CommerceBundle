<?php

namespace Ekyna\Bundle\CommerceBundle;

use Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler as BundlePass;
use Ekyna\Bundle\ResourceBundle\AbstractBundle;
use Ekyna\Component\Commerce;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection as ComponentPass;
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
        $container->addCompilerPass(new ComponentPass\PayumBuilderPass());
        $container->addCompilerPass(new ComponentPass\ShipmentGatewayRegistryPass());
        $container->addCompilerPass(new ComponentPass\NewsletterRegistriesPass());
        $container->addCompilerPass(new ComponentPass\TwigPathCompilerPass());

        $container->addCompilerPass(new BundlePass\AdminMenuPass());
        $container->addCompilerPass(new BundlePass\PayumPass());
        $container->addCompilerPass(new BundlePass\SecurityPass());
        $container->addCompilerPass(new BundlePass\SwapPass());
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelInterfaces()
    {
        return array_replace(DoctrineBundleMapping::getDefaultImplementations(), [
            Commerce\Cart\Model\CartInterface::class                 => 'ekyna_commerce.cart.class',
            Commerce\Cart\Model\CartAddressInterface::class          => 'ekyna_commerce.cart_address.class',
            Commerce\Common\Model\CountryInterface::class            => 'ekyna_commerce.country.class',
            Commerce\Common\Model\CouponInterface::class             => 'ekyna_commerce.coupon.class',
            Commerce\Common\Model\CurrencyInterface::class           => 'ekyna_commerce.currency.class',
            Commerce\Customer\Model\CustomerInterface::class         => 'ekyna_commerce.customer.class',
            Commerce\Customer\Model\CustomerGroupInterface::class    => 'ekyna_commerce.customer_group.class',
            Commerce\Customer\Model\CustomerAddressInterface::class  => 'ekyna_commerce.customer_address.class',
            Commerce\Customer\Model\CustomerContactInterface::class  => 'ekyna_commerce.customer_contact.class',
            Commerce\Newsletter\Model\AudienceInterface::class       => 'ekyna_commerce.audience.class',
            Commerce\Newsletter\Model\MemberInterface::class         => 'ekyna_commerce.member.class',
            Commerce\Order\Model\OrderInterface::class               => 'ekyna_commerce.order.class',
            Commerce\Order\Model\OrderAddressInterface::class        => 'ekyna_commerce.order_address.class',
            Commerce\Payment\Model\PaymentMethodInterface::class     => 'ekyna_commerce.payment_method.class',
            Commerce\Payment\Model\PaymentTermInterface::class       => 'ekyna_commerce.payment_term.class',
            Commerce\Pricing\Model\TaxInterface::class               => 'ekyna_commerce.tax.class',
            Commerce\Pricing\Model\TaxGroupInterface::class          => 'ekyna_commerce.tax_group.class',
            Commerce\Pricing\Model\TaxRuleInterface::class           => 'ekyna_commerce.tax_rule.class',
            Commerce\Quote\Model\QuoteInterface::class               => 'ekyna_commerce.quote.class',
            Commerce\Quote\Model\QuoteAddressInterface::class        => 'ekyna_commerce.quote_address.class',
            Commerce\Shipment\Model\ShipmentMethodInterface::class   => 'ekyna_commerce.shipment_method.class',
            Commerce\Supplier\Model\SupplierInterface::class         => 'ekyna_commerce.supplier.class',
            Commerce\Supplier\Model\SupplierAddressInterface::class  => 'ekyna_commerce.supplier_address.class',
            Commerce\Supplier\Model\SupplierDeliveryInterface::class => 'ekyna_commerce.supplier_delivery.class',
            Commerce\Supplier\Model\SupplierOrderInterface::class    => 'ekyna_commerce.supplier_order.class',
            Commerce\Supplier\Model\SupplierTemplateInterface::class => 'ekyna_commerce.supplier_template.class',
            Commerce\Supplier\Model\SupplierProductInterface::class  => 'ekyna_commerce.supplier_product.class',
            Commerce\Support\Model\TicketInterface::class            => 'ekyna_commerce.ticket.class',
            Commerce\Support\Model\TicketMessageInterface::class     => 'ekyna_commerce.ticket_message.class',
            Commerce\Stock\Model\WarehouseInterface::class           => 'ekyna_commerce.warehouse.class',
        ]);
    }
}
