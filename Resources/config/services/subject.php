<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectLabelRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectOrderExporter;
use Ekyna\Component\Commerce\Subject\Calculator\SubjectCostCalculator;
use Ekyna\Component\Commerce\Subject\Calculator\SubjectCostCalculatorInterface;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesser;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistry;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Subject provider registry
    $services->set('ekyna_commerce.registry.subject_provider', SubjectProviderRegistry::class);

    // Subject cost guesser
    $services
        ->set('ekyna_commerce.calculator.subject_cost', SubjectCostCalculator::class)
        ->args([
            service('ekyna_commerce.guesser.subject_cost'),
            service('ekyna_commerce.repository.bill_of_materials'),
            service('ekyna_commerce.calculator.bill_of_materials'),
        ])
        ->alias(SubjectCostCalculatorInterface::class, 'ekyna_commerce.calculator.subject_cost');

    // Subject cost guesser
    $services
        ->set('ekyna_commerce.guesser.subject_cost', SubjectCostGuesser::class)
        ->args([
            service('ekyna_resource.repository.factory'),
            service('ekyna_commerce.calculator.supplier_order_item'),
            //service('ekyna_commerce.calculator.bill_of_materials'),
            service('ekyna_commerce.converter.currency'),
        ])
        ->alias(SubjectCostGuesserInterface::class, 'ekyna_commerce.guesser.subject_cost');

    // Subject order exporter
    $services
        ->set('ekyna_commerce.exporter.subject_order', SubjectOrderExporter::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.registry.subject_provider'),
            service('ekyna_resource.helper'),
            param('ekyna_commerce.class.order_item_assignment'),
        ]);

    // Subject label renderer
    $services
        ->set('ekyna_commerce.renderer.subject_label', SubjectLabelRenderer::class)
        ->args([
            service('event_dispatcher'),
            service('twig'),
            service('ekyna_resource.generator.pdf'),
            service('translator'),
        ])
        ->tag('twig.runtime');
};
