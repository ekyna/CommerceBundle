<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\DataFixtures\CommerceProcessor;
use Ekyna\Bundle\CommerceBundle\DataFixtures\ORM\CommerceProvider;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Commerce fixtures processor
        ->set('ekyna_commerce.processor.data_fixtures', CommerceProcessor::class)
            ->tag('fidry_alice_data_fixtures.processor')

        // Commerce fixtures provider
        ->set('ekyna_commerce.provider.data_fixtures', CommerceProvider::class)
            ->args([
                service('ekyna_commerce.repository.country'),
                service('ekyna_commerce.repository.currency'),
                service('ekyna_commerce.repository.tax_group'),
                service('ekyna_commerce.repository.customer_group'),
                service('ekyna_commerce.repository.warehouse'),
                service('ekyna_commerce.registry.subject_provider'),
                service('ekyna_resource.factory.factory'),
            ])
            ->tag('nelmio_alice.faker.provider')
    ;
};
