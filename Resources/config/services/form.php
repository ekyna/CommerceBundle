<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Form\Extension\PhoneNumberTypeExtension;
use Ekyna\Bundle\CommerceBundle\Form\PaymentMethodCreateFlow;
use Ekyna\Bundle\CommerceBundle\Form\ShipmentMethodCreateFlow;
use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Ekyna\Bundle\CommerceBundle\Form\SubjectFormBuilder;
use Ekyna\Bundle\CommerceBundle\Form\SupplierOrderCreateFlow;
use Ekyna\Bundle\CommerceBundle\Form\Type\Account\ProfileType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Account\RegistrationType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\ExportType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\BalancePaymentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout\PaymentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ArrayAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\GenderChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MoneyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\BalanceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyModelChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderInvoiceLineType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderInvoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderPaymentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderShipmentParcelType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodFactoryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Report\ReportConfigType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemCreateFlow;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemSubjectType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\GatewayDataType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\RelayPointType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodFactoryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodPickType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentPlatformChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentPricingType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentRuleType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\WarehouseType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierDeliveryType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderItemsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderSubmitType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierProductType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierTemplateChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierType;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Accounting form type
        ->set('ekyna_commerce.form_type.accounting_export', ExportType::class) // TODO Rename to AccountingExportType
            ->args([
                service('ekyna_resource.provider.locale'),
                service('ekyna_commerce.repository.order_invoice'),
            ])
            ->tag('form.type')

        // Address form type
        ->set('ekyna_commerce.form_type.address', AddressType::class)
            ->tag('form.type')

        // Array address form type
        ->set('ekyna_commerce.form_type.array_address', ArrayAddressType::class)
            ->args([
                service('ekyna_commerce.transformer.array_address'),
                service('validator'),
            ])
            ->tag('form.type')

        // Cart form type
        ->set('ekyna_commerce.form_type.cart', CartType::class)
            ->args([
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')
            ->tag('form.js', [ // TODO Define this somewhere else (duplicates)
                'selector' => '.commerce-sale',
                'path'     => 'ekyna-commerce/form/sale',
            ])

        // Country choice form type
        ->set('ekyna_commerce.form_type.country_choice', CountryChoiceType::class)
            ->args([
                service('ekyna_commerce.provider.country'),
                service('ekyna_resource.provider.locale'),
            ])
            ->tag('form.type')

        // Currency choice form type
        ->set('ekyna_commerce.form_type.currency_choice', CurrencyChoiceType::class)
            ->args([
                service('ekyna_commerce.provider.currency'),
                service('ekyna_resource.provider.locale'),
            ])
            ->tag('form.type')

        // Customer balance form type
        ->set('ekyna_commerce.form_type.balance', BalanceType::class)
            ->args([
                service('ekyna_commerce.repository.order'),
            ])
            ->tag('form.type')

        // Customer form type
        ->set('ekyna_commerce.form_type.customer', CustomerType::class)
            ->args([
                service('ekyna_commerce.features'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => 'form[name=customer]',
                'path'     => 'ekyna-commerce/form/customer',
            ])

        // Account registration form type
        ->set('ekyna_commerce.form_type.account.registration', RegistrationType::class)
            ->args([
                service('security.token_storage'),
                service('ekyna_commerce.features'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => 'form[name=registration]',
                'path'     => 'ekyna-commerce/form/registration',
            ])

        // Account information form type
        ->set('ekyna_commerce.form_type.account.profile', ProfileType::class)
            ->args([
                service('ekyna_commerce.features'),
                param('ekyna_commerce.class.customer'),
            ])
            ->tag('form.type')

        // Gender form type
        ->set('ekyna_commerce.form_type.gender', GenderChoiceType::class) // TODO Rename to GenderChoiceType
            ->args([
                param('ekyna_commerce.class.genders'),
            ])
            ->tag('form.type')

        // Money form type
        ->set('ekyna_commerce.form_type.money', MoneyType::class)
            ->args([
                service('ekyna_commerce.converter.currency'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-money',
                'path'     => 'ekyna-commerce/form/money',
            ])

        // Notify form type
        ->set('ekyna_commerce.form_type.notify', NotifyType::class)
            ->args([
                service('ekyna_commerce.helper.notify'),
                service('translator'),
                service('security.authorization_checker'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-notify',
                'path'     => 'ekyna-commerce/form/notify',
            ])

        // Notify model choice form type
        ->set('ekyna_commerce.form_type.notify_model_choice', NotifyModelChoiceType::class)
            ->args([
                service('translator'),
                param('ekyna_commerce.class.notify_model'),
                param('ekyna_resource.locales'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-notify-model',
                'path'     => 'ekyna-commerce/form/notify-model',
            ])

        // Order form type
        ->set('ekyna_commerce.form_type.order', OrderType::class)
            ->args([
                service('security.authorization_checker'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')
            ->tag('form.js', [// TODO Define this somewhere else (duplicates)
                'selector' => '.commerce-sale',
                'path'     => 'ekyna-commerce/form/sale',
            ])

        // Order payment form type
        ->set('ekyna_commerce.form_type.order_payment', OrderPaymentType::class)
            ->args([
                service('ekyna_commerce.checker.locking'),
            ])
            ->tag('form.type')

        // Order shipment form type
        ->set('ekyna_commerce.form_type.order_shipment', OrderShipmentType::class)
            ->args([
                service('ekyna_commerce.builder.shipment'),
                service('security.authorization_checker'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.shipment',
                'path'     => 'ekyna-commerce/form/shipment',
            ])

        // Order shipment parcel form type
        ->set('ekyna_commerce.form_type.order_shipment_parcel', OrderShipmentParcelType::class)
            ->args([
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')

        // Order invoice form type
        ->set('ekyna_commerce.form_type.order_invoice', OrderInvoiceType::class)
            ->args([
                service('ekyna_commerce.builder.invoice'),
                service('ekyna_commerce.checker.locking'),
                service('security.authorization_checker'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.invoice',
                'path'     => 'ekyna-commerce/form/invoice',
            ])

        // Order invoice line form type
        ->set('ekyna_commerce.form_type.order_invoice_line', OrderInvoiceLineType::class)
            ->args([
                service('ekyna_commerce.factory.resolver.invoice_availability'),
            ])
            ->tag('form.type')

        // Phone number (ui) form type extension
        ->set('ekyna_commerce.form_type_extension.phone_number', PhoneNumberTypeExtension::class)
            ->args([
                service('ekyna_commerce.provider.country'),
            ])
            ->tag('form.type_extension')

        // Price form type
        ->set('ekyna_commerce.form_type.price', PriceType::class)
            ->args([
                param('ekyna_commerce.default.currency'),
                param('ekyna_commerce.default.vat_display_mode'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-price',
                'path'     => 'ekyna-commerce/form/price',
            ])

        // Quote form type
        ->set('ekyna_commerce.form_type.quote', QuoteType::class)
            ->args([
                service('security.authorization_checker'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')
            ->tag('form.js', [ // TODO Define this somewhere else (duplicates)
                'selector' => '.commerce-sale',
                'path'     => 'ekyna-commerce/form/sale',
            ])

        // Report config form type
        ->set('ekyna_commerce.form_type.report_config', ReportConfigType::class)
            ->args([
                service('ekyna_commerce.report.registry'),
                service('translator'),
                param('kernel.environment'),
            ])
            ->tag('form.type')

        // Checkout payment balance form type
        ->set('ekyna_commerce.form_type.checkout_payment_balance', BalancePaymentType::class)
            ->args([
                service('ekyna_commerce.updater.payment'),
            ])
            ->tag('form.type')

        // Checkout payment form type
        ->set('ekyna_commerce.form_type.checkout_payment', PaymentType::class)
            ->args([
                service('translator'),
            ])
            ->tag('form.type')

        // Payment method form type
        ->set('ekyna_commerce.form_type.payment_method', PaymentMethodType::class)
            ->args([
                service('payum'),
            ])
            ->tag('form.type')

        // Payment method factory choice form type
        ->set('ekyna_commerce.form_type.payment_method_factory_choice', PaymentMethodFactoryChoiceType::class)
            ->args([
                param('ekyna_commerce.class.payment_method'),
            ])
            ->tag('form.type')

        // Payment method create form flow
        ->set('ekyna_commerce.form_flow.payment_method_create', PaymentMethodCreateFlow::class)
            ->parent('craue.form.flow')

        // Relay point form type
        ->set('ekyna_commerce.form_type.relay_point', RelayPointType::class)
            ->args([
                service('ekyna_commerce.repository.relay_point'),
                service('serializer'),
                param('ekyna_google.api_key'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-relay-point',
                'path'     => 'ekyna-commerce/form/relay-point',
            ])

        // Shipment gateway data form type
        ->set('ekyna_commerce.form_type.shipment_gateway_data', GatewayDataType::class)
            ->args([
                service('ekyna_commerce.registry.shipment_gateway'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-shipment-gateway-data',
                'path'     => 'ekyna-commerce/form/shipment-gateway-data',
            ])

        // Shipment method form type
        ->set('ekyna_commerce.form_type.shipment_method', ShipmentMethodType::class)
            ->args([
                service('ekyna_commerce.registry.shipment_gateway'),
            ])
            ->tag('form.type')

        // Shipment method pick form type
        ->set('ekyna_commerce.form_type.shipment_method_pick', ShipmentMethodPickType::class)
            ->args([
                service('ekyna_commerce.resolver.shipment_price'),
                service('ekyna_commerce.registry.shipment_gateway'),
                service('ekyna_commerce.repository.shipment_method'),
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.factory.formatter'),
                service('translator'),
            ])
            ->tag('form.type')

        // Shipment pricing form type
        ->set('ekyna_commerce.form_type.shipment_pricing', ShipmentPricingType::class)
            ->args([
                param('ekyna_commerce.class.shipment_zone'),
                param('ekyna_commerce.class.shipment_method'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-shipment-pricing',
                'path'     => 'ekyna-commerce/form/shipment-pricing',
            ])

        // Shipment rule form type
        ->set('ekyna_commerce.form_type.shipment_rule', ShipmentRuleType::class)
            ->args([
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('form.type')

        // Shipment platform choice form type
        ->set('ekyna_commerce.form_type.shipment_platform_choice', ShipmentPlatformChoiceType::class)
            ->args([
                service('ekyna_commerce.registry.shipment_gateway'),
            ])
            ->tag('form.type')

        // Shipment method factory choice form type
        ->set('ekyna_commerce.form_type.shipment_method_factory_choice', ShipmentMethodFactoryChoiceType::class)
            ->args([
                param('ekyna_commerce.class.shipment_method'),
            ])
            ->tag('form.type')

        // Shipment method create flow type
        ->set('ekyna_commerce.form_flow.shipment_method_create', ShipmentMethodCreateFlow::class)
            ->parent('craue.form.flow')

        // Subject form builder
        // (previously ekyna_commerce.form_type.subject.builder)
        ->set('ekyna_commerce.builder.subject_form', SubjectFormBuilder::class)

        // Stock subject form builder
        // (previously ekyna_commerce.form_type.subject.builder)
        ->set('ekyna_commerce.builder.stock_subject_form', StockSubjectFormBuilder::class)

        // Stock warehouse form type
        ->set('ekyna_commerce.form_type.warehouse', WarehouseType::class)
            ->args([
                service('security.authorization_checker'),
            ])
            ->tag('form.type')

        // Subject (relative) choice form type
        ->set('ekyna_commerce.form_type.subject_choice', SubjectChoiceType::class)
            ->args([
                service('ekyna_commerce.registry.subject_provider'),
                service('ekyna_resource.helper'),
                service('translator'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-subject-choice',
                'path'     => 'ekyna-commerce/form/subject-choice',
            ])

        // Supplier form type
        ->set('ekyna_commerce.form_type.supplier', SupplierType::class)
            ->args([
                service('ekyna_commerce.repository.supplier_product'),
            ])
            ->tag('form.type')

        // Supplier delivery form type
        ->set('ekyna_commerce.form_type.supplier_delivery', SupplierDeliveryType::class)
            ->args([
                service('ekyna_commerce.factory.supplier_delivery_item'),
                service('ekyna_commerce.helper.subject'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-supplier-delivery',
                'path'     => 'ekyna-commerce/form/supplier-delivery',
            ])

        // Supplier order form type
        ->set('ekyna_commerce.form_type.supplier_order', SupplierOrderType::class)
            ->args([
                service('ekyna_commerce.factory.formatter'),
                param('ekyna_commerce.class.supplier_product'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-supplier-order',
                'path'     => 'ekyna-commerce/form/supplier-order',
            ])
            ->tag('form.js', [
                'selector' => '.commerce-supplier-order',
                'path'     => 'ekyna-commerce/form/supplier-order-compose',
            ])

        // Supplier order items form type
        ->set('ekyna_commerce.form_type.supplier_order_items', SupplierOrderItemsType::class)
            ->args([
                service('ekyna_commerce.factory.supplier_order_item'),
            ])
            ->tag('form.type')

        // Supplier order item form type
        ->set('ekyna_commerce.form_flow.supplier_order_create', SupplierOrderCreateFlow::class)
            ->parent('craue.form.flow')
            ->args([
                service('ekyna_commerce.updater.supplier_order'),
            ])

        // Supplier order submit form type
        ->set('ekyna_commerce.form_type.supplier_order_submit', SupplierOrderSubmitType::class)
            ->args([
                param('ekyna_commerce.class.supplier_order'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-supplier-submit',
                'path'     => 'ekyna-commerce/form/supplier-order',
            ])

        // Supplier order template form type
        ->set('ekyna_commerce.form_type.supplier_order_template', SupplierTemplateChoiceType::class)
            ->args([
                param('ekyna_commerce.class.supplier_template'),
                param('ekyna_resource.locales'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-supplier-template',
                'path'     => 'ekyna-commerce/form/supplier-template-choice',
            ])

        // Supplier product form type
        ->set('ekyna_commerce.form_type.supplier_product', SupplierProductType::class)
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => 'form[name=supplier_product]',
                'path'     => 'ekyna-commerce/form/supplier-product',
            ])

        // Sale create item form flow
        ->set('ekyna_commerce.form_flow.sale_item_add', SaleItemCreateFlow::class)
            ->parent('craue.form.flow')

        // Sale address form type
        ->set('ekyna_commerce.form_type.sale_address', SaleAddressType::class)
            ->args([
                service('serializer'),
                service('ekyna_commerce.repository.customer'),
                service('ekyna_commerce.repository.customer_address'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-sale-address',
                'path'     => 'ekyna-commerce/form/sale-address',
            ])

        // Sale item subject form type
        ->set('ekyna_commerce.form_type.sale_item_subject', SaleItemSubjectType::class)
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-sale-item-subject',
                'path'     => 'ekyna-commerce/form/sale-item-subject',
            ])

        // Sale item configure form type
        ->set('ekyna_commerce.form_type.sale_item_configure', SaleItemConfigureType::class)
            ->args([
                service('ekyna_commerce.helper.sale_item'),
            ])
            ->tag('form.type')

        // Sale shipment form type
        ->set('ekyna_commerce.form_type.sale_shipment', SaleShipmentType::class)
            ->args([
                service('ekyna_commerce.resolver.shipment_price'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-sale-shipment',
                'path'     => 'ekyna-commerce/form/sale-shipment',
            ])

        // Tax group choice form type
        ->set('ekyna_commerce.form_type.tax_group_choice', TaxGroupChoiceType::class)
            ->args([
                service('ekyna_commerce.repository.country'),
            ])
            ->tag('form.type')

        // VAT number form type
        ->set('ekyna_commerce.form_type.vat_number', VatNumberType::class)
            ->args([
                service('router'),
                service('twig'),
            ])
            ->tag('form.type')
            ->tag('form.js', [
                'selector' => '.commerce-vat-number',
                'path'     => 'ekyna-commerce/form/vat-number',
            ])
    ;
};
