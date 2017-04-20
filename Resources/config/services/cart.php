<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\AddToCartEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CartAdjustmentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CartEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CartItemAdjustmentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CartItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CartPaymentEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\GoogleTrackingEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Factory\CartFactory;
use Ekyna\Bundle\CommerceBundle\Service\Cart\SessionCartProvider;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CartAddressEventSubscriber;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Resolver\CartStateResolver;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Cart factory
        ->set('ekyna_commerce.factory.cart', CartFactory::class)
            ->parent('ekyna_commerce.factory.abstract_sale')
            ->call('setExpirationDelay', [param('ekyna_commerce.default.expiration.cart')])

        // Add to cart event subscriber
        ->set('ekyna_commerce.listener.add_to_cart', AddToCartEventSubscriber::class)
            ->args([
                service('ekyna_commerce.helper.subject'),
                service('translator'),
                service('router'),
            ])
            ->tag('kernel.event_subscriber')

        // Google tracking event subscriber
        // TODO If GoogleBundle is available
        ->set('ekyna_commerce.listener.google_tracking', GoogleTrackingEventSubscriber::class)
            ->args([
                service('ekyna_google.tracking.pool'),
                service('ekyna_commerce.factory.amount_calculator'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('kernel.event_subscriber')

        // Cart session provider
        ->set('ekyna_commerce.provider.cart', SessionCartProvider::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.factory.cart'),
                service('ekyna_commerce.repository.cart'),
                service('ekyna_commerce.manager.cart'),
                service('ekyna_commerce.provider.customer'),
                service('ekyna_commerce.provider.currency'),
                service('ekyna_resource.provider.locale'),
                service('request_stack'),
            ])

        // Cart number generator
        ->set('ekyna_commerce.generator.cart_number', DateNumberGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/cart_number'"),
                10,
                '\Cym',
                param('kernel.debug')
            ])

        // Cart state resolver
        ->set('ekyna_commerce.resolver.cart_state', CartStateResolver::class)
            ->factory([service('ekyna_commerce.factory.sale_state_resolver'), 'getResolver'])
            ->args([CartInterface::class])

        // Cart resource event listener
        ->set('ekyna_commerce.listener.cart', CartEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale')
            ->call('setNumberGenerator', [service('ekyna_commerce.generator.cart_number')])
            ->call('setStateResolver', [service('ekyna_commerce.resolver.cart_state')])
            ->call('setExpirationDelay', [param('ekyna_commerce.default.expiration.cart')])
            ->tag('resource.event_subscriber')

        // Cart address resource event listener
        ->set('ekyna_commerce.listener.cart_address', CartAddressEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_address')
            ->tag('resource.event_subscriber')

        // Cart item resource event listener
        ->set('ekyna_commerce.listener.cart_item', CartItemEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_item')
            ->tag('resource.event_subscriber')

        // Cart item adjustment resource event listener
        ->set('ekyna_commerce.listener.cart_item_adjustment', CartItemAdjustmentEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_adjustment')
            ->tag('resource.event_subscriber')

        // Cart adjustment resource event listener
        ->set('ekyna_commerce.listener.cart_adjustment', CartAdjustmentEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_sale_adjustment')
            ->tag('resource.event_subscriber')

        // Cart payment resource event listener
        ->set('ekyna_commerce.listener.cart_payment', CartPaymentEventSubscriber::class)
            ->parent('ekyna_commerce.listener.abstract_payment')
            ->tag('resource.event_subscriber')
    ;
};
