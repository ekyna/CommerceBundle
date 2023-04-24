<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\OrderAdjustmentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\OrderEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\OrderItemAdjustmentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\OrderItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\OrderPaymentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Factory\InvoiceFactory;
use Ekyna\Bundle\CommerceBundle\Factory\OrderFactory;
use Ekyna\Bundle\CommerceBundle\Factory\ShipmentFactory;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderInvoiceExporter;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderItemExporter;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderListExporter;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderAddressEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderInvoiceEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderInvoiceItemEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderInvoiceLineEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderShipmentEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderShipmentItemEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\StockUnitEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\Order\OrderMarginInvalidator;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Resolver\OrderStateResolver;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdater;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Order factory
    $services
        ->set('ekyna_commerce.factory.order', OrderFactory::class)
        ->parent('ekyna_commerce.factory.abstract_sale')
        ->call('setInChargeResolver', [service('ekyna_commerce.resolver.in_charge')]);

    // Order invoice factory
    $services
        ->set('ekyna_commerce.factory.order_invoice', InvoiceFactory::class)
        ->args([
            service('request_stack'),
        ]);

    // Order shipment factory
    $services
        ->set('ekyna_commerce.factory.order_shipment', ShipmentFactory::class)
        ->args([
            service('request_stack'),
        ]);

    // Order state resolver
    $services
        ->set('ekyna_commerce.resolver.state.order', OrderStateResolver::class)
        ->factory([service('ekyna_commerce.factory.sale_state_resolver'), 'getResolver'])
        ->args([OrderInterface::class]);

    // Order number generator
    $services
        ->set('ekyna_commerce.generator.order_number', DateNumberGenerator::class)
        ->args([10, '\Oym', param('kernel.debug'),])
        ->call('setStorage', [
            expr("parameter('kernel.project_dir')~'/var/data/order_number'"),
        ]);

    // Order updater
    $services
        ->set('ekyna_commerce.updater.order', OrderUpdater::class)
        ->args([
            service('ekyna_commerce.factory.margin_calculator'),
        ]);

    // Order margin invalidator
    $services
        ->set('ekyna_commerce.invalidator.order_margin', OrderMarginInvalidator::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
            param('ekyna_commerce.class.order_item_stock_assignment'),
            param('ekyna_commerce.class.order'),
        ])
        ->tag('kernel.event_subscriber');

    // Order (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order', OrderEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_sale')
        ->call('setNumberGenerator', [service('ekyna_commerce.generator.order_number')])
        ->call('setStateResolver', [service('ekyna_commerce.resolver.state.order')])
        ->call('setStockAssigner', [service('ekyna_commerce.assigner.stock_unit')])
        ->call('setOrderRepository', [service('ekyna_commerce.repository.order')])
        ->call('setCouponRepository', [service('ekyna_commerce.repository.coupon')])
        ->call('setInvoicePaymentResolver', [service('ekyna_commerce.resolver.invoice_payment')])
        ->call('setOrderUpdater', [service('ekyna_commerce.updater.order')])
        ->call('setSubjectHelper', [service('ekyna_commerce.helper.subject')])
        ->call('setInChargeResolver', [service('ekyna_commerce.resolver.in_charge')])
        ->tag('resource.event_subscriber');

    // Order address (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_address', OrderAddressEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_sale_address')
        ->tag('resource.event_subscriber');

    // Order item (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_item', OrderItemEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_sale_item')
        ->call('setStockAssigner', [service('ekyna_commerce.assigner.stock_unit')])
        ->tag('resource.event_subscriber');

    // Order item adjustment (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_item_adjustment', OrderItemAdjustmentEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_sale_adjustment')
        ->tag('resource.event_subscriber');

    // Order adjustment (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_adjustment', OrderAdjustmentEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_sale_adjustment')
        ->tag('resource.event_subscriber');

    // Order payment (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_payment', OrderPaymentEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_payment')
        ->call('setLockChecker', [service('ekyna_commerce.checker.locking')])
        ->tag('resource.event_subscriber');

    // Order shipment (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_shipment', OrderShipmentEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_shipment')
        ->tag('resource.event_subscriber');

    // Order shipment item (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_shipment_item', OrderShipmentItemEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_shipment_item')
        ->tag('resource.event_subscriber');

    // Order invoice (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_invoice', OrderInvoiceEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_invoice')
        ->call('setLockChecker', [service('ekyna_commerce.checker.locking')])
        ->tag('resource.event_subscriber');

    // Order invoice item (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_invoice_item', OrderInvoiceItemEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_invoice_item')
        ->call('setLockChecker', [service('ekyna_commerce.checker.locking')])
        ->tag('resource.event_subscriber');

    // Order invoice line (resource) event listener
    $services
        ->set('ekyna_commerce.listener.order_invoice_line', OrderInvoiceLineEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_invoice_line')
        ->call('setLockChecker', [service('ekyna_commerce.checker.locking')])
        ->tag('resource.event_subscriber');

    // Order stock unit (resource) event listener
    $services
        ->set('ekyna_commerce.listener.stock_unit', StockUnitEventSubscriber::class)
        ->args([
            service('ekyna_commerce.invalidator.order_margin'),
        ])
        ->tag('resource.event_subscriber');

    // Order invoice exporter
    $services
        ->set('ekyna_commerce.exporter.order_invoice', OrderInvoiceExporter::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.provider.region'),
            service('translator'),
        ]);

    // Order item exporter
    $services
        ->set('ekyna_commerce.exporter.order_item', OrderItemExporter::class)
        ->args([
            service('doctrine.dbal.default_connection'),
        ]);

    // Order list exporter
    $services
        ->set('ekyna_commerce.exporter.order_list', OrderListExporter::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('translator'),
        ]);
};
