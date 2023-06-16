<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectLabelRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectOrderExporter;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesser;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistry;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Subject provider registry
        ->set('ekyna_commerce.registry.subject_provider', SubjectProviderRegistry::class)

        // Subject cost guesser
        ->set('ekyna_commerce.guesser.subject_cost', SubjectCostGuesser::class)
            ->lazy()
            ->args([
                service('ekyna_resource.repository.factory'),
                service('ekyna_commerce.calculator.supplier_order_item'),
                service('ekyna_commerce.converter.currency'),
            ])
            ->tag('twig.runtime')

        // Subject order exporter
        ->set('ekyna_commerce.exporter.subject_order', SubjectOrderExporter::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_commerce.registry.subject_provider'),
                service('ekyna_resource.helper'),
                param('ekyna_commerce.class.order_item_stock_assignment'),
            ])

        // Subject label renderer
        ->set('ekyna_commerce.renderer.subject_label', SubjectLabelRenderer::class)
            ->args([
                service('event_dispatcher'),
                service('twig'),
                service('ekyna_resource.generator.pdf'),
                service('translator'),
            ])
            ->tag('twig.runtime')
    ;
};
