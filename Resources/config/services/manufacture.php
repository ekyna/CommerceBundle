<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\BillOfMaterialsListener;
use Ekyna\Bundle\CommerceBundle\Service\Manufacture\ManufactureHelper;
use Ekyna\Bundle\CommerceBundle\Service\Manufacture\ManufactureRenderer;
use Ekyna\Component\Commerce\Common\Generator\DefaultGenerator;
use Ekyna\Component\Commerce\Manufacture\Calculator\BillOfMaterialsCalculator;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionCalculator;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionItemCalculator;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionOrderCalculator;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionPriceCalculator;
use Ekyna\Component\Commerce\Manufacture\Event\BillOfMaterialsEvents;
use Ekyna\Component\Commerce\Manufacture\Event\ProductionEvents;
use Ekyna\Component\Commerce\Manufacture\Event\ProductionItemEvents;
use Ekyna\Component\Commerce\Manufacture\Event\ProductionOrderEvents;
use Ekyna\Component\Commerce\Manufacture\EventListener\ProductionItemListener;
use Ekyna\Component\Commerce\Manufacture\EventListener\ProductionListener;
use Ekyna\Component\Commerce\Manufacture\EventListener\ProductionOrderListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Bill Of materials number generator
    $services
        ->set('ekyna_commerce.generator.bill_of_materials_number', DefaultGenerator::class)
        ->args([8, 'BOM'])
        ->call('setStorage', [
            expr("parameter('kernel.project_dir')~'/var/data/bill_of_materials_number'"),
        ]);

    // Bill Of materials event listener
    $services
        ->set('ekyna_commerce.listener.bill_of_materials', BillOfMaterialsListener::class)
        ->args([
            service('ekyna_commerce.generator.bill_of_materials_number'),
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->tag('resource.event_listener', [
            'event' => BillOfMaterialsEvents::INSERT,
            'method' => 'onInsert',
        ])
        ->tag('resource.event_listener', [
            'event' => BillOfMaterialsEvents::PRE_DELETE,
            'method' => 'onPreDelete',
        ]);

    // Production order number generator
    $services
        ->set('ekyna_commerce.generator.production_order_number', DefaultGenerator::class)
        ->args([10, 'PO'])
        ->call('setStorage', [
            expr("parameter('kernel.project_dir')~'/var/data/production_order_number'"),
        ]);

    // Production order event listener
    $services
        ->set('ekyna_commerce.listener.production_order', ProductionOrderListener::class)
        ->args([
            service('ekyna_commerce.generator.production_order_number'),
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.factory.production_item'),
            service('ekyna_commerce.linker.production_order'),
            service('ekyna_commerce.assigner.stock_unit'),
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionOrderEvents::INSERT,
            'method' => 'onInsert',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionOrderEvents::UPDATE,
            'method' => 'onUpdate',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionOrderEvents::DELETE,
            'method' => 'onDelete',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionOrderEvents::PRE_DELETE,
            'method' => 'onPreDelete',
        ]);

    // Production item event listener
    $services
        ->set('ekyna_commerce.listener.production_item', ProductionItemListener::class)
        ->args([
            service('ekyna_commerce.assigner.stock_unit'),
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionItemEvents::INSERT,
            'method' => 'onInsert',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionItemEvents::UPDATE,
            'method' => 'onUpdate',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionItemEvents::DELETE,
            'method' => 'onDelete',
        ]);

    // Production item event listener
    $services
        ->set('ekyna_commerce.listener.production', ProductionListener::class)
        ->args([
            service('ekyna_commerce.linker.production_order'),
            service('ekyna_commerce.assigner.stock_unit'),
            service('ekyna_commerce.calculator.production_order'),
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionEvents::INSERT,
            'method' => 'onInsert',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionEvents::UPDATE,
            'method' => 'onUpdate',
        ])
        ->tag('resource.event_listener', [
            'event'  => ProductionEvents::DELETE,
            'method' => 'onDelete',
        ]);

    // Bill of materials calculator
    $services
        ->set('ekyna_commerce.calculator.bill_of_materials', BillOfMaterialsCalculator::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.guesser.subject_cost'),
            service('ekyna_commerce.repository.bill_of_materials'),
        ])
        ->tag('twig.runtime');

    // Production calculator
    $services
        ->set('ekyna_commerce.calculator.production', ProductionCalculator::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
        ]);

    // Production item calculator
    $services
        ->set('ekyna_commerce.calculator.production_item', ProductionItemCalculator::class);

    // Production order calculator
    $services
        ->set('ekyna_commerce.calculator.production_order', ProductionOrderCalculator::class);

    // Production price calculator
    $services
        ->set('ekyna_commerce.calculator.production_price', ProductionPriceCalculator::class)
        ->args([
            service('ekyna_commerce.calculator.assignable_cost'),
        ])
        ->tag('twig.runtime');

    // Production renderer
    $services
        ->set('ekyna_commerce.renderer.manufacture', ManufactureRenderer::class)
        ->args([
            service('ekyna_commerce.repository.production_order'),
            service('twig'),
        ])
        ->tag('twig.runtime');

    // Production helper
    $services
        ->set('ekyna_commerce.helper.manufacture', ManufactureHelper::class)
        ->args([
            service('ekyna_commerce.calculator.production_order'),
            service('ekyna_commerce.calculator.production_item'),
            service('ekyna_commerce.repository.bill_of_materials'),
        ])
        ->tag('twig.runtime');
};
