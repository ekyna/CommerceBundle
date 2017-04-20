<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\QuoteAdjustmentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\QuoteEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\QuoteItemAdjustmentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\QuoteItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\QuotePaymentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Factory\QuoteFactory;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\QuoteAddressEventSubscriber;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Resolver\QuoteStateResolver;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Quote factory
        ->set('ekyna_commerce.factory.quote', QuoteFactory::class)
            ->parent('ekyna_commerce.factory.abstract_sale')
            ->call('setInChargeResolver', [service('ekyna_commerce.resolver.in_charge')])
            ->call('setExpirationDelay', [param('ekyna_commerce.default.expiration.quote')])

        // Quote number generator
        ->set('ekyna_commerce.generator.quote_number', DateNumberGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/quote_number'"),
                10,
                '\Qym',
                param('kernel.debug')
            ])

        // Quote state resolver
        ->set('ekyna_commerce.resolver.quote_state', QuoteStateResolver::class)
            ->factory([service('ekyna_commerce.factory.sale_state_resolver'), 'getResolver'])
            ->args([QuoteInterface::class])

        // Quote resource event listener
        ->set('ekyna_commerce.listener.quote', QuoteEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale')
            ->call('setNumberGenerator', [service('ekyna_commerce.generator.quote_number')])
            ->call('setStateResolver', [service('ekyna_commerce.resolver.quote_state')])
            ->call('setSubjectHelper', [service('ekyna_commerce.helper.subject')])
            ->call('setInChargeResolver', [service('ekyna_commerce.resolver.in_charge')])
            ->tag('resource.event_subscriber')

        // Quote address resource event listener
        ->set('ekyna_commerce.listener.quote_address', QuoteAddressEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_address')
            ->tag('resource.event_subscriber')

        // Quote item resource event listener
        ->set('ekyna_commerce.listener.quote_item', QuoteItemEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_item')
            ->tag('resource.event_subscriber')

        // Quote item adjustment resource event listener
        ->set('ekyna_commerce.listener.quote_item_adjustment', QuoteItemAdjustmentEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_adjustment')
            ->tag('resource.event_subscriber')

        // Quote adjustment resource event listener
        ->set('ekyna_commerce.listener.quote_adjustment', QuoteAdjustmentEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_adjustment')
            ->tag('resource.event_subscriber')

        // Quote payment resource event listener
        ->set('ekyna_commerce.listener.quote_payment', QuotePaymentEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_payment')
            ->tag('resource.event_subscriber')
    ;
};
