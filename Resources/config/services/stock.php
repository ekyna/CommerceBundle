<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\StockAdjustmentEventSubscriber;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssigner;
use Ekyna\Component\Commerce\Stock\Cache\StockAssignmentCache;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCache;
use Ekyna\Component\Commerce\Stock\Calculator\AssignableCostCalculator;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcher;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Commerce\Stock\Export\StockSubjectLogExporter;
use Ekyna\Component\Commerce\Stock\Linker\ProductionOrderLinker;
use Ekyna\Component\Commerce\Stock\Linker\SupplierOrderLinker;
use Ekyna\Component\Commerce\Stock\Logger\StockLogger;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManager;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManager;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandler;
use Ekyna\Component\Commerce\Stock\Prioritizer\AbstractPrioritizer;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizeChecker;
use Ekyna\Component\Commerce\Stock\Prioritizer\ProductionPrioritizeChecker;
use Ekyna\Component\Commerce\Stock\Prioritizer\ProductionPrioritizer;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizer;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProvider;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolver;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Stock\Updater\StockAssignmentUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdater;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Stock logger
    $services
        ->set('ekyna_commerce.logger.stock', StockLogger::class)
        ->args([
            service('logger'),
        ])
        ->tag('monolog.logger', ['channel' => 'stock']);

    // Stock subject log exporter
    $services
        ->set('ekyna_commerce.exporter.subject_stock_log', StockSubjectLogExporter::class)
        ->args([
            service('ekyna_commerce.repository.supplier_delivery_item'),
            service('ekyna_commerce.repository.order_shipment_item'),
            service('ekyna_commerce.helper.stock_unit'),
        ]);

    // Abstract stock unit listener
    $services
        ->set('ekyna_commerce.listener.abstract_stock_unit', AbstractStockUnitListener::class)
        ->abstract()
        ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
        ->call('setDispatcher', [service('ekyna_resource.event_dispatcher')])
        ->call('setStateResolver', [service('ekyna_commerce.resolver.state.stock_unit')]);

    // Stock adjustment (resource) listener
    $services
        ->set('ekyna_commerce.listener.stock_adjustment', StockAdjustmentEventSubscriber::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.updater.stock_unit'),
        ])
        ->tag('resource.event_subscriber');

    // Stock unit cache
    $services
        ->set('ekyna_commerce.cache.stock_unit', StockUnitCache::class)
        ->tag('resource.event_subscriber');

    // Assignable cost calculator
    $services
        ->set('ekyna_commerce.calculator.assignable_cost', AssignableCostCalculator::class)
        ->lazy() // To prevent 'ekyna_commerce.guesser.subject_cost' cyclic redundancy
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.guesser.subject_cost'),
        ])
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Stock unit resolver
    $services
        ->set('ekyna_commerce.resolver.stock_unit', StockUnitResolver::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.cache.stock_unit'),
            service('ekyna_resource.repository.factory'),
            service('ekyna_resource.factory.factory'),
        ]);

    // Stock unit state resolver
    $services
        ->set('ekyna_commerce.resolver.state.stock_unit', StockUnitStateResolver::class);

    // Stock unit manager
    $services
        ->set('ekyna_commerce.manager.stock_unit', StockUnitManager::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.resolver.state.stock_unit'),
            service('ekyna_commerce.cache.stock_unit'),
        ]);

    // Stock unit updater
    $services
        ->set('ekyna_commerce.updater.stock_unit', StockUnitUpdater::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.manager.stock_unit'),
            service('ekyna_commerce.handler.stock_overflow'),
        ]);

    // Stock assignment cache
    $services
        ->set('ekyna_commerce.cache.stock_assignment', StockAssignmentCache::class);

    // Stock assignment manager
    $services
        ->set('ekyna_commerce.manager.stock_assignment', StockAssignmentManager::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.cache.stock_assignment'),
        ])
        ->tag('resource.event_subscriber');

    // Stock assignment updater
    $services
        ->set('ekyna_commerce.updater.stock_assignment', StockAssignmentUpdater::class)
        ->args([
            service('ekyna_commerce.updater.stock_unit'),
            service('ekyna_commerce.manager.stock_assignment'),
        ]);

    // Stock assignment dispatcher
    $services
        ->set('ekyna_commerce.dispatcher.stock_assignment', StockAssignmentDispatcher::class)
        ->args([
            service('ekyna_commerce.manager.stock_assignment'),
            service('ekyna_commerce.manager.stock_unit'),
            service('ekyna_commerce.logger.stock'),
        ]);

    // Stock overflow handler
    $services
        ->set('ekyna_commerce.handler.stock_overflow', OverflowHandler::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.resolver.stock_unit'),
            service('ekyna_commerce.dispatcher.stock_assignment'),
        ]);

    // Stock unit assigner
    $services
        ->set('ekyna_commerce.assigner.stock_unit', StockUnitAssigner::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.resolver.stock_unit'),
            service('ekyna_commerce.manager.stock_assignment'),
            service('ekyna_commerce.updater.stock_assignment'),
            service('ekyna_commerce.helper.factory'),
            service('ekyna_commerce.helper.subject'),
        ]);

    // Supplier order [stock unit] linker
    $services
        ->set('ekyna_commerce.linker.supplier_order', SupplierOrderLinker::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.calculator.supplier_order_item'),
            service('ekyna_commerce.updater.stock_unit'),
            service('ekyna_commerce.resolver.stock_unit'),
        ]);

    // Production order [stock unit] linker
    $services
        ->set('ekyna_commerce.linker.production_order', ProductionOrderLinker::class)
        ->args([
            service('ekyna_commerce.resolver.stock_unit'),
            service('ekyna_commerce.updater.stock_unit'),
            service('ekyna_commerce.calculator.production_price'),
            service('ekyna_resource.orm.persistence_helper'),
        ]);

    // Stock subject updater
    $services
        ->set('ekyna_commerce.updater.stock_subject', StockSubjectUpdater::class)
        ->args([
            service('ekyna_commerce.resolver.stock_unit'),
            service('ekyna_commerce.repository.supplier_product'),
            abstract_arg('Stock subject defaults'),
        ]);

    // Order stock prioritize checker
    $services
        ->set('ekyna_commerce.prioritizer.checker.order', OrderPrioritizeChecker::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
        ])
        ->tag('twig.runtime');

    // Production order stock prioritize checker
    $services
        ->set('ekyna_commerce.prioritizer.checker.production', ProductionPrioritizeChecker::class)
        ->args([
                   service('ekyna_commerce.helper.subject'),
               ])
        ->tag('twig.runtime');

    // Abstract stock prioritizer
    $services
        ->set('ekyna_commerce.prioritizer.abstract', AbstractPrioritizer::class)
        ->abstract()
        ->args([
            service('ekyna_commerce.resolver.stock_unit'),
            service('ekyna_commerce.assigner.stock_unit'),
            service('ekyna_commerce.manager.stock_unit'),
            service('ekyna_commerce.cache.stock_unit'),
            service('ekyna_commerce.dispatcher.stock_assignment'),
        ]);

    // Order stock prioritizer
    $services
        ->set('ekyna_commerce.prioritizer.order', OrderPrioritizer::class)
        ->parent('ekyna_commerce.prioritizer.abstract');

    // Production order stock prioritizer
    $services
        ->set('ekyna_commerce.prioritizer.production', ProductionPrioritizer::class)
        ->parent('ekyna_commerce.prioritizer.abstract');

    // Stock renderer
    $services
        ->set('ekyna_commerce.renderer.stock', StockRenderer::class)
        ->args([
            service('serializer'),
            service('twig'),
            abstract_arg('Stock view template'),
            abstract_arg('Stock unit list template'),
            abstract_arg('Stock assignment list template'),
            abstract_arg('Stock subject list template'),
        ])
        ->tag('twig.runtime');

    // Warehouse providers
    $services
        ->set('ekyna_commerce.provider.warehouse', WarehouseProvider::class)
        ->args([
            service('ekyna_commerce.repository.warehouse'),
        ])
        ->tag('twig.runtime');
};
