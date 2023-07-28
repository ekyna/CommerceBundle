<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\PriceListBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentAddressResolver;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentPersister;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider\ShipmentGatewayProvider;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ShipmentRuleRepository;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\ShipmentGatewayRegistryPass;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\ShipmentMethodEventSubscriber;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Shipment\Builder\InvoiceSynchronizer;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilder;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCostCalculator;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculator;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculator;
use Ekyna\Component\Commerce\Shipment\Event\ShipmentMethodEvents;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentItemListener;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentListener;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Shipment\Gateway\InStore\InStorePlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\Noop\NoopPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\Virtual\VirtualPlatform;
use Ekyna\Component\Commerce\Shipment\Resolver\AvailabilityResolverFactory;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolver;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentSubjectStateResolver;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Shipment rule repository
    $services
        ->set('ekyna_commerce.repository.shipment_rule', ShipmentRuleRepository::class)
        ->call('setContextProvider', [service('ekyna_commerce.provider.context')])
        ->call('setCalculatorFactory', [service('ekyna_commerce.factory.amount_calculator')]);

    // Shipment number generator
    $services
        ->set('ekyna_commerce.generator.shipment_number', DateNumberGenerator::class)
        ->args([10, 'ym', param('kernel.debug')])
        ->call('setStorage', [
            expr("parameter('kernel.project_dir')~'/var/data/shipment_number'"),
        ]);

    // Shipment subject calculator
    $services
        ->set('ekyna_commerce.calculator.shipment_subject', ShipmentSubjectCalculator::class)
        ->lazy(true)
        ->args([
            service('ekyna_commerce.helper.subject'),
        ])
        ->call('setInvoiceCalculator', [service('ekyna_commerce.calculator.invoice_subject')]);

    // Shipment weight calculator
    $services
        ->set('ekyna_commerce.calculator.shipment_weight', WeightCalculator::class);

    // Shipment cost calculator
    $services
        ->set('ekyna_commerce.calculator.shipment_cost', ShipmentCostCalculator::class)
        ->args([
            service('ekyna_commerce.resolver.shipment_address'),
            service('ekyna_commerce.calculator.shipment_weight'),
            service('ekyna_commerce.resolver.shipment_price'),
            service('ekyna_commerce.converter.currency'),
        ]);

    // Abstract shipment event listener
    $services
        ->set('ekyna_commerce.listener.abstract_shipment', AbstractShipmentListener::class)
        ->abstract(true)
        ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
        ->call('setNumberGenerator', [service('ekyna_commerce.generator.shipment_number')])
        ->call('setWeightCalculator', [service('ekyna_commerce.calculator.shipment_weight')])
        ->call('setStockUnitAssigner', [service('ekyna_commerce.assigner.stock_unit')])
        ->call('setInvoiceSynchronizer', [service('ekyna_commerce.synchronizer.shipment_invoice')]);

    // Abstract shipment item event listener
    $services
        ->set('ekyna_commerce.listener.abstract_shipment_item', AbstractShipmentItemListener::class)
        ->abstract(true)
        ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
        ->call('setStockUnitAssigner', [service('ekyna_commerce.assigner.stock_unit')]);

    // Shipment method (resource) event listener
    $services
        ->set('ekyna_commerce.listener.shipment_method', ShipmentMethodEventSubscriber::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->tag('resource.event_listener', [
            'event'  => ShipmentMethodEvents::INSERT,
            'method' => 'onInsert',
        ])
        ->tag('resource.event_listener', [
            'event'  => ShipmentMethodEvents::PRE_DELETE,
            'method' => 'onPreDelete',
        ])
        ->tag('resource.event_listener', [
            'event'  => ShipmentMethodEvents::DELETE,
            'method' => 'onDelete',
        ]);

    // Shipment builder
    $services
        ->set('ekyna_commerce.builder.shipment', ShipmentBuilder::class)
        ->args([
            service('ekyna_commerce.factory.resolver.shipment_availability'),
            service('ekyna_commerce.helper.factory'),
            service('ekyna_commerce.registry.shipment_gateway'),
        ]);

    // Shipment invoice synchronizer
    $services
        ->set('ekyna_commerce.synchronizer.shipment_invoice', InvoiceSynchronizer::class)
        ->args([
            service('ekyna_commerce.builder.invoice'),
            service('ekyna_commerce.calculator.invoice_subject'),
            service('ekyna_commerce.calculator.invoice'),
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->call('setLockChecker', [service('ekyna_commerce.checker.locking')]);

    // Shipment availability resolver factory
    $services
        ->set('ekyna_commerce.factory.resolver.shipment_availability', AvailabilityResolverFactory::class)
        ->args([
            service('ekyna_commerce.calculator.shipment_subject'),
            service('ekyna_commerce.helper.subject'),
        ]);

    // Shipment price list builder
    $services
        ->set('ekyna_commerce.builder.shipment_price_list', PriceListBuilder::class)
        ->args([
            service('ekyna_commerce.repository.shipment_zone'),
            service('ekyna_commerce.repository.shipment_method'),
            service('ekyna_commerce.repository.shipment_price'),
        ]);

    // Shipment price resolver
    $services
        ->set('ekyna_commerce.resolver.shipment_price', ShipmentPriceResolver::class)
        ->args([
            service('ekyna_commerce.repository.shipment_price'),
            service('ekyna_commerce.repository.shipment_rule'),
            service('ekyna_commerce.repository.shipment_method'),
            service('ekyna_commerce.registry.shipment_gateway'),
            service('ekyna_commerce.resolver.tax'),
            service('ekyna_commerce.provider.context'),
        ])
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Shipment address resolver
    $services
        ->set('ekyna_commerce.resolver.shipment_address', ShipmentAddressResolver::class)
        ->args([
            service('ekyna_commerce.transformer.array_address'),
            service('ekyna_setting.manager'),
        ]);

    // Shipment address resolver
    $services
        ->set('ekyna_commerce.resolver.state.shipment_subject', ShipmentSubjectStateResolver::class)
        ->args([
            service('ekyna_commerce.calculator.shipment_subject'),
        ]);

    // Shipment persister (for gateways)
    $services
        ->set('ekyna_commerce.persister.shipment', ShipmentPersister::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
        ]);

    // Shipment gateway registry
    $services
        ->set('ekyna_commerce.registry.shipment_gateway', GatewayRegistry::class)
        ->lazy(true)
        ->call('setAddressResolver', [service('ekyna_commerce.resolver.shipment_address')])
        ->call('setWeightCalculator', [service('ekyna_commerce.calculator.shipment_weight')])
        ->call('setPersister', [service('ekyna_commerce.persister.shipment')]);

    // Shipment method gateway provider
    $services
        ->set('ekyna_commerce.provider.shipment_method_gateway', ShipmentGatewayProvider::class)
        ->args([
            service('ekyna_commerce.repository.shipment_method'),
        ])
        ->tag(ShipmentGatewayRegistryPass::PROVIDER_TAG);

    // Shipment Noop platform
    $services
        ->set('ekyna_commerce.shipment_platform.noop', NoopPlatform::class)
        ->tag(ShipmentGatewayRegistryPass::PLATFORM_TAG);

    // Shipment In Store platform
    $services
        ->set('ekyna_commerce.shipment_platform.in_store', InStorePlatform::class)
        ->tag(ShipmentGatewayRegistryPass::PLATFORM_TAG);

    // Shipment Virtual platform
    $services
        ->set('ekyna_commerce.shipment_platform.virtual', VirtualPlatform::class)
        ->tag(ShipmentGatewayRegistryPass::PLATFORM_TAG);
};
