<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\StockAdjustmentEventSubscriber;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssigner;
use Ekyna\Component\Commerce\Stock\Cache\StockAssignmentCache;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCache;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcher;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Commerce\Stock\Linker\StockUnitLinker;
use Ekyna\Component\Commerce\Stock\Logger\StockLogger;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManager;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManager;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandler;
use Ekyna\Component\Commerce\Stock\Prioritizer\PrioritizeChecker;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizer;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProvider;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolver;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Stock\Updater\StockAssignmentUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdater;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Stock logger
        ->set('ekyna_commerce.logger.stock', StockLogger::class)
            ->args([
                service('logger'),
            ])
            ->tag('monolog.logger', ['channel' => 'stock'])

        // Abstract stock unit listener
        // TODO (previously ekyna_commerce.stock_unit.abstract_listener)
        ->set('ekyna_commerce.listener.abstract_stock_unit', AbstractStockUnitListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setDispatcher', [service('ekyna_resource.event_dispatcher')])
            ->call('setStateResolver', [service('ekyna_commerce.resolver.state.stock_unit')])

        // Stock adjustment (resource) listener
        ->set('ekyna_commerce.listener.stock_adjustment', StockAdjustmentEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.updater.stock_unit'),
            ])
            ->tag('resource.event_subscriber')

        // Stock unit cache
        ->set('ekyna_commerce.cache.stock_unit', StockUnitCache::class)
            ->tag('resource.event_subscriber')

        // Stock unit resolver
        ->set('ekyna_commerce.resolver.stock_unit', StockUnitResolver::class)
            ->args([
                service('ekyna_commerce.helper.subject'),
                service('ekyna_commerce.cache.stock_unit'),
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.factory.factory'),
            ])

        // Stock unit state resolver
        ->set('ekyna_commerce.resolver.state.stock_unit', StockUnitStateResolver::class)

        // Stock unit manager
        ->set('ekyna_commerce.manager.stock_unit', StockUnitManager::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.resolver.state.stock_unit'),
                service('ekyna_commerce.cache.stock_unit'),
            ])

        // Stock unit updater
        ->set('ekyna_commerce.updater.stock_unit', StockUnitUpdater::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.resolver.stock_unit'),
                service('ekyna_commerce.manager.stock_unit'),
                service('ekyna_commerce.handler.stock_overflow'),
            ])

        // Stock assignment cache
        ->set('ekyna_commerce.cache.stock_assignment', StockAssignmentCache::class)

        // Stock assignment manager
        ->set('ekyna_commerce.manager.stock_assignment', StockAssignmentManager::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.cache.stock_assignment'),
                service('ekyna_commerce.helper.factory'),
            ])
            ->tag('resource.event_subscriber')

        // Stock assignment updater
        ->set('ekyna_commerce.updater.stock_assignment', StockAssignmentUpdater::class)
            ->args([
                service('ekyna_commerce.updater.stock_unit'),
                service('ekyna_commerce.manager.stock_assignment'),
            ])

        // Stock assignment dispatcher
        ->set('ekyna_commerce.dispatcher.stock_assignment', StockAssignmentDispatcher::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.manager.stock_assignment'),
                service('ekyna_commerce.manager.stock_unit'),
                service('ekyna_commerce.logger.stock'),
            ])

        // Stock overflow handler
        ->set('ekyna_commerce.handler.stock_overflow', OverflowHandler::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.resolver.stock_unit'),
                service('ekyna_commerce.dispatcher.stock_assignment'),
            ])

        // Stock unit assigner
        ->set('ekyna_commerce.assigner.stock_unit', StockUnitAssigner::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.resolver.stock_unit'),
                service('ekyna_commerce.manager.stock_assignment'),
                service('ekyna_commerce.updater.stock_assignment'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.helper.subject'),
            ])

        // Stock unit linker
        ->set('ekyna_commerce.linker.stock_unit', StockUnitLinker::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.calculator.supplier_order'),
                service('ekyna_commerce.updater.stock_unit'),
                service('ekyna_commerce.resolver.stock_unit'),
                service('ekyna_commerce.converter.currency'),
            ])

        // Stock subject updater
        ->set('ekyna_commerce.updater.stock_subject', StockSubjectUpdater::class)
            ->args([
                service('ekyna_commerce.resolver.stock_unit'),
                service('ekyna_commerce.repository.supplier_product'),
                abstract_arg('Stock subject defaults'),
            ])

        // Stock prioritize checker
        ->set('ekyna_commerce.prioritizer.checker', PrioritizeChecker::class)
            ->args([
                service('ekyna_commerce.helper.subject'),
            ])
            ->tag('twig.runtime')

        // Stock prioritizer
        ->set('ekyna_commerce.prioritizer.stock', StockPrioritizer::class)
            ->args([
                service('ekyna_commerce.resolver.stock_unit'),
                service('ekyna_commerce.assigner.stock_unit'),
                service('ekyna_commerce.manager.stock_unit'),
                service('ekyna_commerce.cache.stock_unit'),
                service('ekyna_commerce.manager.stock_assignment'),
                service('ekyna_commerce.dispatcher.stock_assignment'),
                service('ekyna_commerce.prioritizer.checker'),
            ])

        // Stock renderer
        ->set('ekyna_commerce.renderer.stock', StockRenderer::class)
            ->lazy(true)
            ->args([
                service('serializer'),
                service('twig'),
                abstract_arg('Stock unit list template'),
                abstract_arg('Stock assignment list template'),
                abstract_arg('Stock subject list template'),
            ])
            ->tag('twig.runtime')

        // Warehouse provider
        ->set('ekyna_commerce.provider.warehouse', WarehouseProvider::class)
            ->args([
                service('ekyna_commerce.repository.warehouse'),
            ])
            ->tag('twig.runtime')
    ;
};
