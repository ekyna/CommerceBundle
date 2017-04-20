<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\AccountDashboardSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\AccountMenuSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CustomerAddressEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CustomerEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\CustomerGroupEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\RegistrationEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Factory\CustomerFactory;
use Ekyna\Bundle\CommerceBundle\Service\Customer\SecurityCustomerProvider;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Customer\Balance\BalanceBuilder;
use Ekyna\Component\Commerce\Customer\Export\CustomerExporter;
use Ekyna\Component\Commerce\Customer\Import\AddressImporter;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdater;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Customer number generator
        ->set('ekyna_commerce.generator.customer_number', DateNumberGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/customer_number'"),
                10,
                '\C\Uym',
                param('kernel.debug'),
            ])

        // Customer updater
        ->set('ekyna_commerce.updater.customer', CustomerUpdater::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])

        // Customer balance builder
        ->set('ekyna_commerce.builder.customer_balance', BalanceBuilder::class)
            ->args([
                service('ekyna_commerce.repository.order_invoice'),
                service('ekyna_commerce.repository.order_payment'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.resolver.due_date'),
            ])

        // Customer exporter
        ->set('ekyna_commerce.exporter.customer', CustomerExporter::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                param('ekyna_commerce.class.order'),
            ])

        // Customer address importer
        ->set('ekyna_commerce.importer.customer_address', AddressImporter::class)
            ->args([
                service('ekyna_commerce.factory.customer_address'),
                service('ekyna_commerce.repository.country'),
                service('libphonenumber\PhoneNumberUtil'),
                service('validator'),
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_commerce.listener.customer_address'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.terminate', 'method' => 'flush'])

        // Customer provider
        ->set('ekyna_commerce.provider.customer', SecurityCustomerProvider::class)
            ->args([
                service('ekyna_commerce.repository.customer_group'),
                service('ekyna_commerce.repository.customer'),
                service('ekyna_user.provider.user'),
            ])

        // Customer factory
        ->set('ekyna_commerce.factory.customer', CustomerFactory::class)
            ->args([
                service('ekyna_commerce.provider.currency'),
                service('ekyna_resource.provider.locale'),
                service('ekyna_commerce.repository.customer_group'),
                service('ekyna_commerce.resolver.in_charge'),
            ])

        // Customer (resource) event listener
        ->set('ekyna_commerce.listener.customer', CustomerEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.generator.customer_number'),
                service('ekyna_commerce.generator.key'),
                service('ekyna_commerce.updater.pricing'),
                service('ekyna_resource.event_dispatcher'),
            ])
            ->tag('resource.event_subscriber')

        // Customer address (resource) event listener
        ->set('ekyna_commerce.listener.customer_address', CustomerAddressEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.customer_address'),
            ])
            ->tag('resource.event_subscriber')

        // Customer group (resource) event listener
        ->set('ekyna_commerce.listener.customer_group', CustomerGroupEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.customer_group'),
            ])
            ->tag('resource.event_subscriber')

        // Account menu event listener
        ->set('ekyna_commerce.listener.account_menu', AccountMenuSubscriber::class)
            ->args([
                service('ekyna_commerce.provider.customer'),
                service('ekyna_commerce.features'),
            ])
            ->tag('kernel.event_subscriber')

        // Account dashboard event listener
        ->set('ekyna_commerce.listener.account_dashboard', AccountDashboardSubscriber::class)
            ->args([
                service('ekyna_commerce.provider.customer'),
                service('ekyna_commerce.repository.quote'),
                service('ekyna_commerce.repository.order'),
                service('ekyna_commerce.repository.order_invoice'),
            ])
            ->tag('kernel.event_subscriber')

        // Registration event listener
        ->set('ekyna_commerce.listener.registration', RegistrationEventSubscriber::class)
            ->args([
                service('ekyna_commerce.mailer'),
                service('translator'),
                service('ekyna_commerce.helper.newsletter_subscription')->nullOnInvalid(),
            ])
            ->tag('kernel.event_subscriber')
    ;
};
