<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentLinesHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentPageBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Document builder
    $services
        ->set('ekyna_commerce.builder.document', DocumentBuilder::class)
        ->lazy()
        ->args([
            service('ekyna_resource.provider.locale'),
            service('ekyna_commerce.transformer.array_address'),
            service('libphonenumber\PhoneNumberUtil'),
        ]);

    // Document calculator
    $services
        ->set('ekyna_commerce.calculator.document', DocumentCalculator::class)
        ->lazy()
        ->args([
            service('ekyna_commerce.factory.amount_calculator'),
            service('ekyna_commerce.converter.currency'),
            service('ekyna_commerce.factory.formatter'),
        ]);

    // Document renderer factory
    $services
        ->set('ekyna_commerce.factory.document_renderer', RendererFactory::class)
        ->lazy()
        ->args([
            service('twig'),
            service('ekyna_resource.generator.pdf'),
            abstract_arg('Renderer factory configuration'),
        ]);

    // Document helper
    $services
        ->set('ekyna_commerce.helper.document', DocumentHelper::class)
        ->args([
            service('ekyna_setting.manager'),
            service('ekyna_commerce.filesystem'),
            service('router'),
            service('ekyna_commerce.renderer.common'),
            service('ekyna_commerce.resolver.tax'),
            service('ekyna_commerce.helper.subject'),
            service('event_dispatcher'),
            abstract_arg('Document helper configuration'),
            param('kernel.default_locale'),
        ])
        ->tag('twig.runtime');

    // Document lines helper
    $services
        ->set('ekyna_commerce.helper.document_lines', DocumentLinesHelper::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.calculator.shipment_subject'),
            abstract_arg('Document page builder configuration'),
        ])
        ->tag('twig.runtime');

    // Document generator
    $services
        ->set('ekyna_commerce.generator.document', DocumentGenerator::class)
        ->lazy()
        ->args([
            service('ekyna_commerce.builder.document'),
            service('ekyna_commerce.calculator.document'),
            service('ekyna_commerce.factory.document_renderer'),
            service('ekyna_commerce.helper.factory'),
            service('translator'),
        ]);
};
