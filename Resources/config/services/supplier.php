<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\SupplierDeliveryEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierDeliveryItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierOrderEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SupplierOrderItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierOrderExporter;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\SupplierProductEventSubscriber;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculator;
use Ekyna\Component\Commerce\Supplier\EventListener\AbstractListener;
use Ekyna\Component\Commerce\Supplier\Factory\SupplierOrderFactory;
use Ekyna\Component\Commerce\Supplier\Factory\SupplierProductFactory;
use Ekyna\Component\Commerce\Supplier\Resolver\SupplierOrderStateResolver;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdater;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Supplier order state resolver
        ->set('ekyna_commerce.resolver.state.supplier_order', SupplierOrderStateResolver::class)

        // Supplier order number generator
        ->set('ekyna_commerce.generator.supplier_order_number', DateNumberGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/supplier_order_number'"),
                10,
                '\Sym',
                param('kernel.debug'),
            ])

        // Supplier order calculator
        ->set('ekyna_commerce.calculator.supplier_order', SupplierOrderCalculator::class)
            ->args([
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.resolver.tax'),
            ])
            ->tag('doctrine.event_listener', [
                'event'      => 'onClear',
                'connection' => 'default',
            ])

        // Supplier order updater
        ->set('ekyna_commerce.updater.supplier_order', SupplierOrderUpdater::class)
            ->args([
                service('ekyna_commerce.generator.supplier_order_number'),
                service('ekyna_commerce.resolver.state.supplier_order'),
                service('ekyna_commerce.calculator.supplier_order'),
                service('ekyna_commerce.converter.currency'),
            ])

        // Supplier order factory
        ->set('ekyna_commerce.factory.supplier_order', SupplierOrderFactory::class)
            ->args([
                service('ekyna_commerce.repository.warehouse'),
                service('ekyna_commerce.updater.supplier_order'),
            ])

        // Supplier product factory
        ->set('ekyna_commerce.factory.supplier_product', SupplierProductFactory::class)
            ->args([
                service('ekyna_commerce.repository.tax_group'),
            ])

        // Supplier abstract (resource) event listener
        ->set('ekyna_commerce.listener.abstract_supplier', AbstractListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setStockUnitLinker', [service('ekyna_commerce.linker.stock_unit')])
            ->call('setStockUnitUpdater', [service('ekyna_commerce.updater.stock_unit')])

        // Supplier order (resource) event listener
        ->set('ekyna_commerce.listener.supplier_order', SupplierOrderEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_supplier')
            ->args([
                service('ekyna_commerce.updater.supplier_order'),
            ])
            ->tag('resource.event_subscriber')

        // Supplier order item (resource) event listener
        ->set('ekyna_commerce.listener.supplier_order_item', SupplierOrderItemEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_supplier')
            ->tag('resource.event_subscriber')

        // Supplier delivery (resource) event listener
        ->set('ekyna_commerce.listener.supplier_delivery', SupplierDeliveryEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_supplier')
            ->tag('resource.event_subscriber')

        // Supplier delivery item (resource) event listener
        ->set('ekyna_commerce.listener.supplier_delivery_item', SupplierDeliveryItemEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_supplier')
            ->tag('resource.event_subscriber')

        // Supplier delivery item (resource) event listener
        ->set('ekyna_commerce.exporter.supplier_order', SupplierOrderExporter::class)
            ->args([
                service('ekyna_commerce.repository.supplier_order'),
                service('translator'),
            ])
    ;
};
