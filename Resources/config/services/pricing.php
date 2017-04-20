<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\TaxGroupEventSubscriber;
use Ekyna\Component\Commerce\Pricing\Api\PricingApi;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolver;
use Ekyna\Component\Commerce\Pricing\Updater\PricingUpdater;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Tax group (resource) event listener
        ->set('ekyna_commerce.listener.tax_group', TaxGroupEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.tax_group'),
            ])
            ->tag('resource.event_subscriber')

        // Tax resolver
        ->set('ekyna_commerce.resolver.tax', TaxResolver::class)
            ->args([
                service('ekyna_commerce.provider.country'),
                service('ekyna_commerce.provider.warehouse'),
                service('ekyna_commerce.repository.tax_rule'),
            ])

        // Pricing updater
        ->set('ekyna_commerce.updater.pricing', PricingUpdater::class)
            ->args([
                service('ekyna_commerce.api.pricing'),
            ])

        // Pricing API
        ->set('ekyna_commerce.api.pricing', PricingApi::class)
            ->args([
                abstract_arg('Pricing API providers'),
            ])
    ;
};
