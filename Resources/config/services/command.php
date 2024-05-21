<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Command\AccountingExportCommand;
use Ekyna\Bundle\CommerceBundle\Command\AddressMigrateCommand;
use Ekyna\Bundle\CommerceBundle\Command\CartPurgeCommand;
use Ekyna\Bundle\CommerceBundle\Command\CartTransformCommand;
use Ekyna\Bundle\CommerceBundle\Command\CustomerBalanceIntegrityCommand;
use Ekyna\Bundle\CommerceBundle\Command\CustomerBirthdayCommand;
use Ekyna\Bundle\CommerceBundle\Command\CustomerFlagsUpdateCommand;
use Ekyna\Bundle\CommerceBundle\Command\FixVoucherNumberCommand;
use Ekyna\Bundle\CommerceBundle\Command\InvoiceDueDateUpdateCommand;
use Ekyna\Bundle\CommerceBundle\Command\InvoiceNumberIntegrityCommand;
use Ekyna\Bundle\CommerceBundle\Command\InvoicePaidTotalUpdateCommand;
use Ekyna\Bundle\CommerceBundle\Command\InvoiceUpdateDataCommand;
use Ekyna\Bundle\CommerceBundle\Command\InvoiceUpdateMarginCommand;
use Ekyna\Bundle\CommerceBundle\Command\InvoiceUpdateTotalsCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrderDateModifyCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrderDetachCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrdersStateCheckCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrderStateUpdateCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrderUpdateMarginCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrderUpdateTotalsCommand;
use Ekyna\Bundle\CommerceBundle\Command\OrderWatchCommand;
use Ekyna\Bundle\CommerceBundle\Command\PaymentStateChangeCommand;
use Ekyna\Bundle\CommerceBundle\Command\PaymentWatchCommand;
use Ekyna\Bundle\CommerceBundle\Command\ReportGenerateCommand;
use Ekyna\Bundle\CommerceBundle\Command\ReportRequestPurgeCommand;
use Ekyna\Bundle\CommerceBundle\Command\ShipmentLabelPurgeCommand;
use Ekyna\Bundle\CommerceBundle\Command\StatCalculateCommand;
use Ekyna\Bundle\CommerceBundle\Command\StatUpdateCommand;
use Ekyna\Bundle\CommerceBundle\Command\StockIntegrityCommand;
use Ekyna\Bundle\CommerceBundle\Command\StockUnitPriceUpdateCommand;
use Ekyna\Bundle\CommerceBundle\Command\SupplierOrderGeneratePaymentCommand;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Accounting export command
    $services
        ->set('ekyna_commerce.command.accounting_export', AccountingExportCommand::class)
        ->args([
            service('ekyna_commerce.exporter.accounting'),
            service('ekyna_setting.manager'),
            service('mailer'),
        ])
        ->tag('console.command');

    // Cart purge command
    $services
        ->set('ekyna_commerce.command.cart_purge', CartPurgeCommand::class)
        ->args([
            service('ekyna_commerce.repository.cart'),
            service('ekyna_commerce.manager.cart'),
        ])
        ->tag('console.command');

    // Cart transform command
    $services
        ->set('ekyna_commerce.command.cart_transform', CartTransformCommand::class)
        ->args([
            service('ekyna_commerce.repository.cart'),
            service('ekyna_commerce.factory.order'),
            service('ekyna_commerce.transformer.sale'),
        ])
        ->tag('console.command');

    // Customer balance integrity command
    $services
        ->set('ekyna_commerce.command.customer_balance_integrity', CustomerBalanceIntegrityCommand::class)
        ->args([
            service('doctrine.dbal.default_connection'),
            service('mailer'), // TODO Report* mailer
            param('ekyna_resource.report_email'),
        ])
        ->tag('console.command');

    // Customer birthday command
    $services
        ->set('ekyna_commerce.command.customer_birthday', CustomerBirthdayCommand::class)
        ->args([
            service('ekyna_commerce.repository.customer'),
            service('ekyna_resource.event_dispatcher'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Customer birthday command
    $services
        ->set('ekyna_commerce.command.customer_flags_update', CustomerFlagsUpdateCommand::class)
        ->args([
            service('ekyna_resource.registry.resource'),
            service('ekyna_commerce.helper.customer.flag_helper'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Fix duplicate order voucher number command
    $services
        ->set('ekyna_commerce.command.fix_order_duplicate_voucher_number', FixVoucherNumberCommand::class)
        ->args([
            service('doctrine'),
            param('ekyna_commerce.class.order'),
        ])
        ->tag('console.command');

    // Invoice due date update command
    $services
        ->set('ekyna_commerce.command.invoice_due_date_update', InvoiceDueDateUpdateCommand::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.resolver.due_date'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Invoice number integrity command
    $services
        ->set('ekyna_commerce.command.invoice_number_integrity', InvoiceNumberIntegrityCommand::class)
        ->args([
            service('doctrine'),
            param('ekyna_commerce.class.order_invoice'),
        ])
        ->tag('console.command');

    // Invoice paid total update command
    $services
        ->set('ekyna_commerce.command.invoice_paid_total_update', InvoicePaidTotalUpdateCommand::class)
        ->args([
            service('ekyna_commerce.resolver.invoice_payment'),
            service('doctrine.orm.default_entity_manager'),
            param('ekyna_commerce.class.order_invoice'),
        ])
        ->tag('console.command');

    // Invoice recalculate command
    $services
        ->set('ekyna_commerce.command.invoice_update_totals', InvoiceUpdateTotalsCommand::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.calculator.invoice'),
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.checker.locking'),
        ])
        ->tag('console.command');

    // Invoice update data command
    $services
        ->set('ekyna_commerce.command.invoice_update_data', InvoiceUpdateDataCommand::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.checker.locking'),
            service('ekyna_commerce.builder.invoice'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Invoice update margin command
    $services
        ->set('ekyna_commerce.command.invoice_update_margin', InvoiceUpdateMarginCommand::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.updater.order_invoice'),
            service('ekyna_commerce.manager.order_invoice'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Modify order date command
    $services
        ->set('ekyna_commerce.command.order_date_modify', OrderDateModifyCommand::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Order detach command
    $services
        ->set('ekyna_commerce.command.detach', OrderDetachCommand::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.assigner.stock_unit'),
        ])
        ->tag('console.command');

    // Order state update command
    $services
        ->set('ekyna_commerce.command.order_state_check', OrdersStateCheckCommand::class)
        ->args([
            service('ekyna_commerce.resolver.state.order'),
            service('doctrine.orm.default_entity_manager'),
            param('ekyna_commerce.class.order'),
        ])
        ->tag('console.command');

    // Order state update command
    $services
        ->set('ekyna_commerce.command.order_state_update', OrderStateUpdateCommand::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('ekyna_commerce.resolver.state.order'),
            service('ekyna_commerce.manager.order'),
            service('translator'),
        ])
        ->tag('console.command');

    // Order update margin command
    $services
        ->set('ekyna_commerce.command.order_update_margin', OrderUpdateMarginCommand::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('ekyna_commerce.updater.order'),
            service('ekyna_commerce.manager.order'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Order update totals command
    $services
        ->set('ekyna_commerce.command.order_totals_update', OrderUpdateTotalsCommand::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.updater.sale'),
            service('ekyna_commerce.updater.order'),
            service('ekyna_commerce.queue.notify'),
            param('ekyna_commerce.class.order'),
        ])
        ->tag('console.command');

    // Order watch command
    $services
        ->set('ekyna_commerce.command.order_watch', OrderWatchCommand::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('ekyna_resource.event_dispatcher'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Payment state change command
    $services
        ->set('ekyna_commerce.command.payment_state_change', PaymentStateChangeCommand::class)
        ->args([
            service('ekyna_resource.repository.factory'),
            service('ekyna_resource.manager.factory'),
            service('ekyna_commerce.checker.locking'),
        ])
        ->tag('console.command');

    // Payment watch command
    $services
        ->set('ekyna_commerce.command.payment_watch', PaymentWatchCommand::class)
        ->args([
            service('ekyna_commerce.watcher.outstanding'),
            service('ekyna_commerce.repository.order_payment'),
            service('ekyna_commerce.repository.quote_payment'),
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_setting.manager'),
            service('mailer.mailer'),
        ])
        ->tag('console.command');

    // Generate report command
    $services
        ->set('ekyna_commerce.command.report_generate', ReportGenerateCommand::class)
        ->args([
            service('ekyna_commerce.report.mailer'),
            service('ekyna_commerce.report.registry'),
        ])
        ->tag('console.command');

    // Report request purge command
    $services
        ->set('ekyna_commerce.command.report_request_purge', ReportRequestPurgeCommand::class)
        ->args([
            service('ekyna_admin.repository.user'),
            service('ekyna_commerce.repository.report_request'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Migrate shipment address command
    $services
        ->set('ekyna_commerce.command.shipment_address_migrate', AddressMigrateCommand::class)
        ->args([
            service('doctrine.dbal.default_connection'),
            service('libphonenumber\PhoneNumberUtil'),
        ])
        ->tag('console.command');

    // Shipment label purge command
    $services
        ->set('ekyna_commerce.command.shipment_label_purge', ShipmentLabelPurgeCommand::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
            abstract_arg('Retention duration'),
        ])
        ->tag('console.command');

    // Stat calculate command
    $services
        ->set('ekyna_commerce.command.stat_calculate', StatCalculateCommand::class)
        ->args([
            service('ekyna_commerce.calculator.stat'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Stat update command
    $services
        ->set('ekyna_commerce.command.stat_update', StatUpdateCommand::class)
        ->args([
            service('ekyna_commerce.updater.stat'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Stock integrity command
    $services
        ->set('ekyna_commerce.command.stock_integrity', StockIntegrityCommand::class)
        ->args([
            service('doctrine.dbal.default_connection'),
            service('ekyna_commerce.updater.stock_subject'),
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.handler.stock_overflow'),
            service('mailer'), // TODO Report* mailer
            param('ekyna_resource.report_email'),
        ])
        ->tag('console.command');

    // Stock unit price update command
    $services
        ->set('ekyna_commerce.command.stock_unit_price_update', StockUnitPriceUpdateCommand::class)
        ->args([
            service('ekyna_commerce.calculator.supplier_order_item'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');

    // Supplier order generate payment command
    $services
        ->set('ekyna_commerce.command.supplier_order.generate_payments', SupplierOrderGeneratePaymentCommand::class)
        ->args([
            service('ekyna_commerce.updater.supplier_order'),
            service('ekyna_commerce.converter.currency'),
            service('ekyna_resource.registry.resource'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('console.command');
};
