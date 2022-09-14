<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Customer;
use Ekyna\Bundle\CommerceBundle\Action\Admin\CustomerAddress;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Order;
use Ekyna\Bundle\CommerceBundle\Action\Admin\NotifyModel;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Payment;
use Ekyna\Bundle\CommerceBundle\Action\Admin\PaymentMethod;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment;
use Ekyna\Bundle\CommerceBundle\Action\Admin\ShipmentMethod;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;
use Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierProduct;
use Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Form flow actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.payment_method.create', PaymentMethod\CreateAction::class)
            ->args([
                service('ekyna_commerce.form_flow.payment_method_create'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.shipment_method.create', ShipmentMethod\CreateAction::class)
            ->args([
                service('ekyna_commerce.form_flow.shipment_method_create'),
            ])
            ->tag('ekyna_resource.action')

        // Attachment actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.attachment.download', Attachment\DownloadAction::class)
            ->args([
                service('ekyna_commerce.filesystem'),
            ])
            ->tag('ekyna_resource.action')

        // Customer actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.customer.balance', Customer\BalanceAction::class)
            ->args([
                service('ekyna_commerce.builder.customer_balance'),
                service('ekyna_commerce.mailer'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.customer.export', Customer\ExportAction::class)
            ->args([
                service('ekyna_commerce.exporter.customer'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.customer.import', Customer\ImportAction::class)
            ->args([
                service('ekyna_resource.importer.csv'),
                service('libphonenumber\PhoneNumberUtil'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.customer.initiator_export', Customer\InitiatorExportAction::class)
            ->args([
                service('ekyna_commerce.exporter.initiator_customer'),
            ])
            ->tag('ekyna_resource.action')

        // Customer address actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.customer_address.import', CustomerAddress\ImportAction::class)
            ->args([
                service('ekyna_resource.importer.csv'),
                service('libphonenumber\PhoneNumberUtil'),
            ])
            ->tag('ekyna_resource.action')

        // Invoice actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.invoice.recalculate', Invoice\RecalculateAction::class)
            ->args([
                service('ekyna_commerce.synchronizer.shipment_invoice'),
                service('ekyna_commerce.builder.invoice'),
                service('ekyna_commerce.calculator.document'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.invoice.render', Invoice\RenderAction::class)
            ->args([
                service('ekyna_commerce.factory.document_renderer'),
                service('ekyna_commerce.calculator.document'),
            ])
            ->tag('ekyna_resource.action')

        // NotifyModel actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.notify_model.test', NotifyModel\TestAction::class)
            ->args([
                service('ekyna_setting.manager'),
                service('ekyna_commerce.builder.notify'),
                service('ekyna_commerce.queue.notify'),
            ])
            ->tag('ekyna_resource.action')

        // Order actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.order.abort', Order\AbortAction::class)
            ->args([
                service('ekyna_commerce.preparer.sale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.order.export_to_deliver', Order\ExportToDeliverAction::class)
            ->args([
                service('ekyna_commerce.exporter.subject_order'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.order.prepare', Order\PrepareAction::class)
            ->args([
                service('ekyna_commerce.preparer.sale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.order.prioritize', Order\PrioritizeAction::class)
            ->args([
                service('ekyna_commerce.prioritizer.stock'),
            ])
            ->tag('ekyna_resource.action')

        // Payment actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.payment.create', Payment\CreateAction::class)
            ->args([
                service('ekyna_commerce.validator.sale_step'),
                service('ekyna_commerce.manager.payment_checkout'),
                service('ekyna_commerce.helper.payment'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.payment.action', Payment\ActionAction::class)
            ->args([
                service('ekyna_commerce.helper.payment'),
            ])
            ->tag('ekyna_resource.action')

        // Payment method actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.payment_method.create', PaymentMethod\CreateAction::class)
            ->args([
                service('ekyna_commerce.form_flow.payment_method_create'),
            ])
            ->tag('ekyna_resource.action')

        // Sale actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.sale.document_generate', Sale\DocumentGenerateAction::class)
            ->args([
                service('ekyna_commerce.generator.document'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.document_render', Sale\DocumentRenderAction::class)
            ->args([
                service('ekyna_commerce.builder.document'),
                service('ekyna_commerce.calculator.document'),
                service('ekyna_commerce.factory.document_renderer'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.duplicate', Sale\DuplicateAction::class)
            ->args([
                service('ekyna_commerce.factory.sale_copier'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.export', Sale\ExportAction::class)
            ->args([
                service('ekyna_commerce.exporter.sale_csv'),
                service('ekyna_commerce.exporter.sale_xls'),
                param('kernel.debug'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.notify', Sale\NotifyAction::class)
            ->args([
                service('ekyna_commerce.builder.notify'),
                service('ekyna_commerce.queue.notify'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.notify_model', Sale\NotifyModelAction::class)
            ->args([
                service('ekyna_commerce.repository.notify_model'),
                param('kernel.default_locale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.recalculate', Sale\RecalculateAction::class)
            ->args([
                service('ekyna_commerce.updater.sale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.set_exchange_rate', Sale\SetExchangeRateAction::class)
            ->args([
                service('ekyna_commerce.updater.sale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.transform', Sale\TransformAction::class)
            ->args([
                service('ekyna_commerce.transformer.sale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale.update_state', Sale\UpdateStateAction::class)
            ->args([
                service('ekyna_commerce.updater.sale'),
                service('ekyna_commerce.factory.sale_state_resolver'),
                param('kernel.debug'),
            ])
            ->tag('ekyna_resource.action')

        // Sale Attachment actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.sale_attachment.generate', Sale\Attachment\GenerateAction::class)
            ->args([
                service('ekyna_commerce.generator.document'),
            ])
            ->tag('ekyna_resource.action')

        // Sale Item actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.sale_item_add', Item\AddAction::class)
            ->args([
                service('ekyna_commerce.form_flow.sale_item_add'),
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.helper.sale'),
                service('event_dispatcher'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale_item_configure', Item\ConfigureAction::class)
            ->args([
                service('event_dispatcher'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale_item_create', Item\CreateAction::class)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.helper.sale'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale_item_prioritize', Item\PrioritizeAction::class)
            ->args([
                service('ekyna_commerce.prioritizer.checker'),
                service('ekyna_commerce.prioritizer.stock'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.sale_item_sync_subject', Item\SyncSubjectAction::class)
            ->args([
                service('ekyna_commerce.helper.sale_item'),
            ])
            ->tag('ekyna_resource.action')

        // Shipment actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.shipment_gateway', Shipment\GatewayAction::class)
            ->args([
                service('ekyna_commerce.registry.shipment_gateway'),
                service('ekyna_commerce.persister.shipment'),
                service('ekyna_commerce.renderer.shipment_label'),
                param('kernel.debug'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.shipment_render', Shipment\RenderAction::class)
            ->args([
                service('ekyna_commerce.factory.document_renderer'),
            ])
            ->tag('ekyna_resource.action')

        // Subject actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.subject.create', Subject\CreateSupplierProductAction::class)
            ->args([
                service('ekyna_commerce.helper.subject'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.subject.export_orders', Subject\ExportOrdersAction::class)
            ->args([
                service('ekyna_commerce.exporter.subject_order'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.subject.label', Subject\LabelAction::class)
            ->args([
                service('ekyna_commerce.renderer.subject_label'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.subject.refresh_stock', Subject\RefreshStockAction::class)
            ->args([
                service('ekyna_commerce.updater.stock_subject'),
            ])
            ->tag('ekyna_resource.action')

        // Supplier product actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.supplier_product.create', SupplierProduct\CreateAction::class)
            ->args([
                service('ekyna_commerce.helper.subject'),
            ])
            ->tag('ekyna_resource.action')

        // Supplier order actions --------------------------------------------------------------------

        ->set('ekyna_commerce.action.supplier_order.create', SupplierOrder\CreateAction::class)
            ->args([
                service('ekyna_commerce.form_flow.supplier_order_create'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.supplier_order.label', SupplierOrder\LabelAction::class)
            ->args([
                service('ekyna_commerce.helper.subject'),
                service('ekyna_commerce.renderer.subject_label'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.supplier_order.notify', SupplierOrder\NotifyAction::class)
            ->args([
                service('ekyna_commerce.builder.notify'),
                service('ekyna_commerce.queue.notify'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.supplier_order.recalculate', SupplierOrder\RecalculateAction::class)
            ->args([
                service('ekyna_commerce.updater.supplier_order'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.supplier_order.render', SupplierOrder\RenderAction::class)
            ->args([
                service('ekyna_commerce.factory.document_renderer'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.supplier_order.submit', SupplierOrder\SubmitAction::class)
            ->args([
                service('ekyna_commerce.mailer'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_commerce.action.supplier_order.template', SupplierOrder\TemplateAction::class)
            ->args([
                service('ekyna_commerce.factory.formatter'),
                param('kernel.default_locale'),
            ])
            ->tag('ekyna_resource.action')
    ;
};
