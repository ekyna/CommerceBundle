<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\AntiFraudEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\PaymentCheckoutEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\PaymentMethodEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Watcher\OutstandingWatcher;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\PaymentDoneEventSubscriber;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculator;
use Ekyna\Component\Commerce\Payment\EventListener\AbstractPaymentListener;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactory;
use Ekyna\Component\Commerce\Payment\Releaser\OutstandingReleaser;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolver;
use Ekyna\Component\Commerce\Payment\Resolver\PaymentSubjectStateResolver;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdater;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Payment number generator
        ->set('ekyna_commerce.generator.payment_number', DateNumberGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/payment_number'"),
                10,
                'ym',
                param('kernel.debug'),
            ])

        // Payment calculator
        ->set('ekyna_commerce.calculator.payment', PaymentCalculator::class)
            ->args([
                service('ekyna_commerce.factory.amount_calculator'),
                service('ekyna_commerce.converter.currency'),
            ])

        // Payment updater
        ->set('ekyna_commerce.updater.payment', PaymentUpdater::class)
            ->args([
                service('ekyna_commerce.converter.currency'),
            ])

        // Payment factory
        ->set('ekyna_commerce.factory.payment', PaymentFactory::class)
            ->args([
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.updater.payment'),
                service('ekyna_commerce.calculator.payment'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.repository.currency'),
            ])

        // Payment outstanding releaser
        ->set('ekyna_commerce.releaser.outstanding', OutstandingReleaser::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.updater.payment'),
                param('ekyna_commerce.default.currency'),
            ])

        // Payment outstanding watcher
        ->set('ekyna_commerce.watcher.outstanding', OutstandingWatcher::class)
            ->args([
                service('ekyna_commerce.repository.payment_term'),
                service('ekyna_commerce.repository.payment_method'),
            ])
            ->call('setManager', [service('doctrine.orm.default_entity_manager')])
            ->call('setResourceHelper', [service('ekyna_resource.helper')])

        // Payment abstract listener
        ->set('ekyna_commerce.listener.abstract_payment', AbstractPaymentListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setNumberGenerator', [service('ekyna_commerce.generator.payment_number')])
            ->call('setKeyGenerator', [service('ekyna_commerce.generator.key')])
            ->call('setPaymentUpdater', [service('ekyna_commerce.updater.payment')])
            ->call('setCustomerUpdater', [service('ekyna_commerce.updater.customer')])

        // Payment checkout manager
        ->set('ekyna_commerce.manager.payment_checkout', CheckoutManager::class)
            ->args([
                service('ekyna_commerce.repository.payment_method'),
                service('ekyna_commerce.factory.payment'),
                service('event_dispatcher'),
            ])

        // Payment checkout event listener
        ->set('ekyna_commerce.listener.payment_checkout', PaymentCheckoutEventSubscriber::class)
            ->args([
                service('form.factory'),
                service('ekyna_commerce.updater.payment'),
                service('ekyna_commerce.converter.currency'),
                service('translator'),
                service('router'),
            ])
            ->tag('kernel.event_subscriber')

        // Payment done event listener
        ->set('ekyna_commerce.listener.payment_done', PaymentDoneEventSubscriber::class)
            ->args([
                service('ekyna_commerce.transformer.sale'),
                service('ekyna_commerce.factory.order'),
                service('payum'),
            ])
            ->tag('kernel.event_subscriber')

        // Payment anti fraud event listener
        ->set('ekyna_commerce.listener.payment_anti_fraud', AntiFraudEventSubscriber::class)
            ->args([
                service('ekyna_commerce.manager.cart'),
                service('doctrine.orm.default_entity_manager'),
                service('payum'),
                service('ekyna_commerce.mailer'),
                param('ekyna_commerce.default.fraud'),
            ])
            ->tag('kernel.event_subscriber')

        // Payment method (resource) event listener
        ->set('ekyna_commerce.listener.payment_method', PaymentMethodEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Payment due date resolver
        ->set('ekyna_commerce.resolver.due_date', DueDateResolver::class)

        // Payment subject state resolver
        ->set('ekyna_commerce.resolver.state.payment_subject', PaymentSubjectStateResolver::class)
            ->args([
                service('ekyna_commerce.calculator.payment'),
                service('ekyna_commerce.converter.currency'),
            ])
    ;
};
