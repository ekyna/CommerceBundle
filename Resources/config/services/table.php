<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Table\Action;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\CommerceBundle\Table\Extension;
use Ekyna\Bundle\CommerceBundle\Table\Filter;
use Ekyna\Bundle\CommerceBundle\Table\Type;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Order prepare action type
    $services
        ->set('ekyna_commerce.table_column_action.order_prepare', Action\OrderPrepareActionType::class)
        ->args([
            service('ekyna_commerce.preparer.sale'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('table.action_type');

    // Order abort action type
    $services
        ->set('ekyna_commerce.table_column_action.order_abort', Action\OrderAbortActionType::class)
        ->args([
            service('ekyna_commerce.preparer.sale'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('table.action_type');

    // Shipment ship action type
    $services
        ->set('ekyna_commerce.table_column_action.shipment_ship', Action\ShipmentShipActionType::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
            service('ekyna_commerce.persister.shipment'),
        ])
        ->tag('table.action_type');

    // Shipment print label action type
    $services
        ->set('ekyna_commerce.table_column_action.shipment_print_label', Action\ShipmentPrintLabelActionType::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
            service('ekyna_commerce.persister.shipment'),
            service('ekyna_commerce.renderer.shipment_label'),
        ])
        ->tag('table.action_type');

    // Shipment cancel action type
    $services
        ->set('ekyna_commerce.table_column_action.shipment_cancel', Action\ShipmentCancelActionType::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
            service('ekyna_commerce.persister.shipment'),
        ])
        ->tag('table.action_type');

    // Shipment prepare action type
    $services
        ->set('ekyna_commerce.table_column_action.shipment_prepare', Action\ShipmentPrepareActionType::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('table.action_type');

    // Shipment prepare action type
    $services
        ->set('ekyna_commerce.table_column_action.shipment_remove', Action\ShipmentRemoveActionType::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('table.action_type');

    // Shipment document action type
    $services
        ->set('ekyna_commerce.table_column_action.shipment_document', Action\ShipmentDocumentActionType::class)
        ->args([
            service('router'),
        ])
        ->tag('table.action_type');

    // Invoice document action type
    $services
        ->set('ekyna_commerce.table_column_action.invoice_document', Action\InvoiceDocumentActionType::class)
        ->args([
            service('router'),
        ])
        ->tag('table.action_type');

    // Cart(s) column type
    $services
        ->set('ekyna_commerce.table_column_type.cart', Column\CartType::class)
        ->args([
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Currency column type
    $services
        ->set('ekyna_commerce.table_column_type.currency', Column\CurrencyType::class)
        ->args([
            service('ekyna_commerce.renderer.currency'),
        ])
        ->tag('table.column_type');

    // Customer flags column type
    $services
        ->set('ekyna_commerce.table_column_type.customer_flags', Column\CustomerFlagsType::class)
        ->args([
            service('ekyna_commerce.renderer.flag'),
        ])
        ->tag('table.column_type');

    // Customer outstanding column type
    $services
        ->set('ekyna_commerce.table_column_type.customer_outstanding', Column\CustomerOutstandingType::class)
        ->args([
            service('ekyna_commerce.factory.formatter'),
            param('ekyna_commerce.default.currency'),
        ])
        ->tag('table.column_type');

    // Customer state column type
    $services
        ->set('ekyna_commerce.table_column_type.customer_state', Column\CustomerStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Notify model type column type
    $services
        ->set('ekyna_commerce.table_column_type.notify_model_type', Column\NotifyModelTypeType::class)
        ->args([
            service('translator'),
        ])
        ->tag('table.column_type');

    // Order column type
    $services
        ->set('ekyna_commerce.table_column_type.order', Column\OrderType::class)
        ->args([
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Order invoice(s) column type
    $services
        ->set('ekyna_commerce.table_column_type.order_invoice', Column\OrderInvoiceType::class)
        ->args([
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Order shipment(s) column type
    $services
        ->set('ekyna_commerce.table_column_type.order_shipment', Column\OrderShipmentType::class)
        ->args([
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Payment state column type
    $services
        ->set('ekyna_commerce.table_column_type.payment_state', Column\PaymentStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Quote(s) column type
    $services
        ->set('ekyna_commerce.table_column_type.quote', Column\QuoteType::class)
        ->args([
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Sale customer column type
    $services
        ->set('ekyna_commerce.table_column_type.sale_customer', Column\SaleCustomerType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Sale flags column type
    $services
        ->set('ekyna_commerce.table_column_type.sale_flags', Column\SaleFlagsType::class)
        ->args([
            service('ekyna_commerce.renderer.flag'),
        ])
        ->tag('table.column_type');

    // Sale state column type
    $services
        ->set('ekyna_commerce.table_column_type.sale_state', Column\SaleStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Shipment state column type
    $services
        ->set('ekyna_commerce.table_column_type.shipment_state', Column\ShipmentStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Shipment weight column type
    $services
        ->set('ekyna_commerce.table_column_type.shipment_weight', Column\ShipmentWeightType::class)
        ->args([
            service('ekyna_commerce.helper.shipment'),
        ])
        ->tag('table.column_type');

    // Shipment tracking number column type
    $services
        ->set('ekyna_commerce.table_column_type.shipment_tracking_number', Column\ShipmentTrackingNumberType::class)
        ->args([
            service('ekyna_commerce.helper.shipment'),
        ])
        ->tag('table.column_type');

    // Invoice paid total column type
    $services
        ->set('ekyna_commerce.table_column_type.invoice_paid_total', Column\InvoicePaidTotalType::class)
        ->args([
            service('ekyna_commerce.factory.formatter'),
            service('ekyna_commerce.renderer.currency'),
            service('ekyna_commerce.resolver.due_date'),
        ])
        ->tag('table.column_type');

    // Invoice state column type
    $services
        ->set('ekyna_commerce.table_column_type.invoice_state', Column\InvoiceStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Shipment actions column type
    $services
        ->set('ekyna_commerce.table_column_type.shipment_actions', Column\ShipmentActionsType::class)
        ->args([
            service('ekyna_commerce.helper.shipment'),
            service('ekyna_resource.helper'),
        ])
        ->tag('table.column_type');

    // Stock subject state column type
    $services
        ->set('ekyna_commerce.table_column_type.stock_subject_state', Column\StockSubjectStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Supplier order payment column type
    $services
        ->set('ekyna_commerce.table_column_type.supplier_order_payment', Column\SupplierOrderPaymentType::class)
        ->args([
            service('ekyna_commerce.renderer.supplier'),
        ])
        ->tag('table.column_type');

    // Supplier order tracking column type
    $services
        ->set('ekyna_commerce.table_column_type.supplier_order_tracking', Column\SupplierOrderTrackingType::class)
        ->args([
            service('translator'),
        ])
        ->tag('table.column_type');

    // Supplier order state column type
    $services
        ->set('ekyna_commerce.table_column_type.supplier_order_state', Column\SupplierOrderStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Ticket state column type
    $services
        ->set('ekyna_commerce.table_column_type.ticket_state', Column\TicketStateType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // VAT display mode column type
    $services
        ->set('ekyna_commerce.table_column_type.vat_display_mode', Column\VatDisplayModeType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Stock subject mode column type
    $services
        ->set('ekyna_commerce.table_column_type.stock_subject_mode', Column\StockSubjectModeType::class)
        ->args([
            service('ekyna_commerce.helper.constants'),
        ])
        ->tag('table.column_type');

    // Customer filter type
    $services
        ->set('ekyna_commerce.table_filter_type.customer', Filter\CustomerType::class)
        ->args([
            param('ekyna_commerce.class.customer'),
        ])
        ->tag('table.filter_type');

    // In charge filter type
    $services
        ->set('ekyna_commerce.table_filter_type.in_charge', Filter\InChargeType::class)
        ->args([
            service('ekyna_admin.repository.group'),
            param('ekyna_admin.class.user'),
        ])
        ->tag('table.filter_type');

    // In charge filter type
    $services
        ->set('ekyna_commerce.table_filter_type.in_charge', Filter\InChargeType::class)
        ->args([
            service('ekyna_admin.repository.group'),
            param('ekyna_admin.class.user'),
        ])
        ->tag('table.filter_type');

    // Sale tags filter type
    $services
        ->set('ekyna_commerce.table_filter_type.sale_tags', Filter\SaleTagsType::class)
        ->args([
            param('ekyna_cms.class.tag'),
        ])
        ->tag('table.filter_type');

    // Sale subject filter type
    $services
        ->set('ekyna_commerce.table_filter_type.sale_subject', Filter\SaleSubjectType::class)
        ->args([
            service('ekyna_commerce.registry.subject_provider'),
        ])
        ->tag('table.filter_type');

    // Price column type extension
    $services
        ->set('ekyna_commerce.table_column_type_extension.price', Extension\PriceTypeExtension::class)
        ->args([
            param('ekyna_commerce.default.currency'),
        ])
        ->tag('table.column_type_extension');

    // Customer table type
    $services
        ->set('ekyna_commerce.table_type.customer', Type\CustomerType::class)
        ->args([
            service('ekyna_commerce.features'),
        ])
        ->tag('table.type');

    // Order invoice table type
    $services
        ->set('ekyna_commerce.table_type.order_invoice', Type\OrderInvoiceType::class)
        ->args([
            service('security.authorization_checker'),
            service('ekyna_commerce.checker.locking'),
        ])
        ->tag('table.type');
};
