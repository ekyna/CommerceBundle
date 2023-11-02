<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierDeliveryEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierDeliveryItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierOrderEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierOrderItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierOrderExporter;
use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierOrderItemExporter;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculator;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculator;
use Ekyna\Component\Commerce\Supplier\Calculator\WeightingCalculator;
use Ekyna\Component\Commerce\Supplier\Event\SupplierPaymentEvents;
use Ekyna\Component\Commerce\Supplier\EventListener\AbstractListener;
use Ekyna\Component\Commerce\Supplier\EventListener\SupplierPaymentListener;
use Ekyna\Component\Commerce\Supplier\Factory\SupplierOrderFactory;
use Ekyna\Component\Commerce\Supplier\Factory\SupplierProductFactory;
use Ekyna\Component\Commerce\Supplier\Resolver\SupplierOrderStateResolver;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdater;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Supplier order state resolver
    $services
        ->set('ekyna_commerce.resolver.state.supplier_order', SupplierOrderStateResolver::class);

    // Supplier order number generator
    $services
        ->set('ekyna_commerce.generator.supplier_order_number', DateNumberGenerator::class)
        ->args([10, '\Sym', param('kernel.debug')])
        ->call('setStorage', [
            expr("parameter('kernel.project_dir')~'/var/data/supplier_order_number'"),
        ]);

    // Supplier order weighting calculator
    $services
        ->set('ekyna_commerce.calculator.supplier_order_weighting', WeightingCalculator::class)
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Supplier order item calculator
    $services
        ->set('ekyna_commerce.calculator.supplier_order_item', SupplierOrderItemCalculator::class)
        ->args([
            service('ekyna_commerce.calculator.supplier_order_weighting'),
            service('ekyna_commerce.converter.currency'),
        ]);

    // Supplier order calculator
    $services
        ->set('ekyna_commerce.calculator.supplier_order', SupplierOrderCalculator::class)
        ->args([
            service('ekyna_commerce.calculator.supplier_order_weighting'),
            service('ekyna_commerce.resolver.tax'),
            param('ekyna_commerce.default.currency'),
        ]);

    // Supplier order updater
    $services
        ->set('ekyna_commerce.updater.supplier_order', SupplierOrderUpdater::class)
        ->args([
            service('ekyna_commerce.generator.supplier_order_number'),
            service('ekyna_commerce.resolver.state.supplier_order'),
            service('ekyna_commerce.calculator.supplier_order'),
        ]);

    // Supplier order factory
    $services
        ->set('ekyna_commerce.factory.supplier_order', SupplierOrderFactory::class)
        ->args([
            service('ekyna_commerce.repository.warehouse'),
            service('ekyna_commerce.updater.supplier_order'),
        ]);

    // Supplier product factory
    $services
        ->set('ekyna_commerce.factory.supplier_product', SupplierProductFactory::class)
        ->args([
            service('ekyna_commerce.repository.tax_group'),
        ]);

    // Supplier abstract (resource) event listener
    $services
        ->set('ekyna_commerce.listener.abstract_supplier', AbstractListener::class)
        ->abstract()
        ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
        ->call('setStockUnitLinker', [service('ekyna_commerce.linker.stock_unit')])
        ->call('setStockUnitUpdater', [service('ekyna_commerce.updater.stock_unit')]);

    // Supplier order (resource) event listener
    $services
        ->set('ekyna_commerce.listener.supplier_order', SupplierOrderEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_supplier')
        ->args([
            service('ekyna_commerce.updater.supplier_order'),
        ])
        ->tag('resource.event_subscriber');

    // Supplier order item (resource) event listener
    $services
        ->set('ekyna_commerce.listener.supplier_order_item', SupplierOrderItemEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_supplier')
        ->tag('resource.event_subscriber');

    // Supplier delivery (resource) event listener
    $services
        ->set('ekyna_commerce.listener.supplier_delivery', SupplierDeliveryEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_supplier')
        ->tag('resource.event_subscriber');

    // Supplier delivery item (resource) event listener
    $services
        ->set('ekyna_commerce.listener.supplier_delivery_item', SupplierDeliveryItemEventSubscriber::class)
        ->parent('ekyna_commerce.listener.abstract_supplier')
        ->tag('resource.event_subscriber');

    // Supplier payment (resource) event listener
    $services
        ->set('ekyna_commerce.listener.supplier_payment', SupplierPaymentListener::class)
        ->args([
            service('ekyna_commerce.converter.currency'),
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->tag('resource.event_listener', [
            'event'  => SupplierPaymentEvents::INSERT,
            'method' => 'onInsert',
        ])
        ->tag('resource.event_listener', [
            'event'  => SupplierPaymentEvents::UPDATE,
            'method' => 'onUpdate',
        ])
        ->tag('resource.event_listener', [
            'event'  => SupplierPaymentEvents::DELETE,
            'method' => 'onDelete',
        ]);

    // Supplier order exporter
    $services
        ->set('ekyna_commerce.exporter.supplier_order', SupplierOrderExporter::class)
        ->args([
            service('ekyna_commerce.repository.supplier_order'),
            service('translator'),
        ]);

    // Supplier order item exporter
    $services
        ->set('ekyna_commerce.exporter.supplier_order_item', SupplierOrderItemExporter::class)
        ->args([
            service('ekyna_commerce.repository.supplier_order_item'),
            service('ekyna_commerce.converter.currency'),
            service('translator'),
        ]);
};
