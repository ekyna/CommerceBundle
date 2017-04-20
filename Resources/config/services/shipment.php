<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\PriceListBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentAddressResolver;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentPersister;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider\ShipmentGatewayProvider;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ShipmentRuleRepository;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\ShipmentGatewayRegistryPass;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\ShipmentMethodEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\Transformer\ShipmentAddressTransformer;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Shipment\Builder\InvoiceSynchronizer;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilder;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculator;
use Ekyna\Component\Commerce\Shipment\Calculator\WeightCalculator;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentItemListener;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentListener;
use Ekyna\Component\Commerce\Shipment\Gateway\InStore\InStorePlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\Noop\NoopPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolver;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentSubjectStateResolver;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Shipment rule repository
        ->set('ekyna_commerce.repository.shipment_rule', ShipmentRuleRepository::class)
            ->call('setContextProvider', [service('ekyna_commerce.provider.context')])
            ->call('setCalculatorFactory', [service('ekyna_commerce.factory.amount_calculator')])

        // Shipment address transformer
        ->set('ekyna_commerce.transformer.shipment_address', ShipmentAddressTransformer::class)
            ->args([
                service('ekyna_commerce.repository.country'),
            ])

        // Shipment number generator
        ->set('ekyna_commerce.generator.shipment_number', DateNumberGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/shipment_number'"),
                10,
                'ym',
                param('kernel.debug'),
            ])

        // Shipment subject calculator
        ->set('ekyna_commerce.calculator.shipment_subject', ShipmentSubjectCalculator::class)
            // TODO ->lazy(true)
            ->args([
                service('ekyna_commerce.helper.subject'),
            ])
            ->call('setInvoiceCalculator', [service('ekyna_commerce.calculator.invoice_subject')])

        // Shipment weight calculator
        ->set('ekyna_commerce.calculator.shipment_weight', WeightCalculator::class)

        // Abstract shipment event listener
        ->set('ekyna_commerce.listener.abstract_shipment', AbstractShipmentListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setNumberGenerator', [service('ekyna_commerce.generator.shipment_number')])
            ->call('setWeightCalculator', [service('ekyna_commerce.calculator.shipment_weight')])
            ->call('setStockUnitAssigner', [service('ekyna_commerce.assigner.stock_unit')])
            ->call('setInvoiceSynchronizer', [service('ekyna_commerce.synchronizer.shipment_invoice')])

        // Abstract shipment item event listener
        ->set('ekyna_commerce.listener.abstract_shipment_item', AbstractShipmentItemListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setStockUnitAssigner', [service('ekyna_commerce.assigner.stock_unit')])

        // Shipment method (resource) event listener
        ->set('ekyna_commerce.listener.shipment_method', ShipmentMethodEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Shipment builder
        ->set('ekyna_commerce.builder.shipment', ShipmentBuilder::class)
            ->args([
                service('ekyna_commerce.factory.sale'),
                service('ekyna_commerce.registry.shipment_gateway'),
                service('ekyna_commerce.calculator.shipment_subject'),
            ])

        // Shipment invoice synchronizer
        ->set('ekyna_commerce.synchronizer.shipment_invoice', InvoiceSynchronizer::class)
            ->args([
                service('ekyna_commerce.builder.invoice'),
                service('ekyna_commerce.calculator.document'),
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->call('setLockingHelper', [service('ekyna_commerce.checker.locking')])

        // Shipment price list builder
        ->set('ekyna_commerce.builder.shipment_price_list', PriceListBuilder::class)
            ->args([
                service('ekyna_commerce.repository.shipment_zone'),
                service('ekyna_commerce.repository.shipment_method'),
                service('ekyna_commerce.repository.shipment_price'),
            ])

        // Shipment price resolver
        ->set('ekyna_commerce.resolver.shipment_price', ShipmentPriceResolver::class)
            ->args([
                service('ekyna_commerce.repository.shipment_price'),
                service('ekyna_commerce.repository.shipment_rule'),
                service('ekyna_commerce.registry.shipment_gateway'),
                service('ekyna_commerce.resolver.tax'),
                service('ekyna_commerce.provider.context'),
            ])
            ->tag('doctrine.event_listener', [
                'event'      => 'onClear',
                'connection' => 'default',
            ])

        // Shipment address resolver
        ->set('ekyna_commerce.resolver.shipment_address', ShipmentAddressResolver::class)
            ->args([
                service('ekyna_commerce.transformer.shipment_address'),
                service('ekyna_setting.manager'),
            ])

        // Shipment address resolver
        ->set('ekyna_commerce.resolver.state.shipment_subject', ShipmentSubjectStateResolver::class)
            ->args([
                service('ekyna_commerce.calculator.shipment_subject'),
            ])

        // Shipment persister (for gateways)
        ->set('ekyna_commerce.persister.shipment', ShipmentPersister::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
            ])

        // Shipment gateway registry
        ->set('ekyna_commerce.registry.shipment_gateway', GatewayRegistry::class)
            // TODO ? ->lazy(true)
            ->call('setAddressResolver', [service('ekyna_commerce.resolver.shipment_address')])
            ->call('setWeightCalculator', [service('ekyna_commerce.calculator.shipment_weight')])
            ->call('setPersister', [service('ekyna_commerce.persister.shipment')])

        // Shipment method gateway provider
        ->set('ekyna_commerce.provider.shipment_method_gateway', ShipmentGatewayProvider::class)
            ->args([
                service('ekyna_commerce.repository.shipment_method'),
            ])
            ->tag(ShipmentGatewayRegistryPass::PROVIDER_TAG)

        // Shipment Noop platform
        ->set('ekyna_commerce.shipment_platform.noop', NoopPlatform::class)
            ->tag(ShipmentGatewayRegistryPass::PLATFORM_TAG)

        // Shipment In Store platform
        ->set('ekyna_commerce.shipment_platform.in_store', InStorePlatform::class)
            ->tag(ShipmentGatewayRegistryPass::PLATFORM_TAG)
    ;
};
