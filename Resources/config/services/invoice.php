<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\Service\Invoice\InvoiceArchiver;
use Ekyna\Component\Commerce\Common\Generator\DefaultGenerator;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilder;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCostCalculator;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculator;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceItemListener;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceLineListener;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceListener;
use Ekyna\Component\Commerce\Invoice\Resolver\AvailabilityResolverFactory;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolver;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoiceSubjectStateResolver;
use Ekyna\Component\Resource\Event\QueueEvents;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Invoice number generator
        ->set('ekyna_commerce.generator.invoice_number', DefaultGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/invoice_number'"),
                10,
                'I',
                param('kernel.debug')
            ])

        // Credit number generator
        ->set('ekyna_commerce.generator.credit_number', DefaultGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/credit_number'"),
                10,
                'C',
                param('kernel.debug')
            ])

        // Invoice subject calculator
        ->set('ekyna_commerce.calculator.invoice_subject', InvoiceSubjectCalculator::class)
            ->args([
                service('ekyna_commerce.converter.currency'),
            ])
            ->call('setShipmentCalculator', [service('ekyna_commerce.calculator.shipment_subject')])

        // Invoice cost calculator
        ->set('ekyna_commerce.calculator.invoice_cost', InvoiceCostCalculator::class)

        // Invoice abstract listener
        ->set('ekyna_commerce.listener.abstract_invoice', AbstractInvoiceListener::class)
            ->abstract()
            ->lazy()
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setInvoiceNumberGenerator', [service('ekyna_commerce.generator.invoice_number')])
            ->call('setCreditNumberGenerator', [service('ekyna_commerce.generator.credit_number')])
            ->call('setInvoiceBuilder', [service('ekyna_commerce.builder.invoice')])
            ->call('setInvoiceCalculator', [service('ekyna_commerce.calculator.document')])
            ->call('setInvoicePaymentResolver', [service('ekyna_commerce.resolver.invoice_payment')])
            ->call('setAuthorizationChecker', [service('security.authorization_checker')])

        // Invoice line abstract listener
        ->set('ekyna_commerce.listener.abstract_invoice_line', AbstractInvoiceLineListener::class)
            ->abstract()
            ->lazy()
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setStockUnitAssigner', [service('ekyna_commerce.assigner.stock_unit')])
            ->call('setAuthorizationChecker', [service('security.authorization_checker')])

        // Invoice item abstract listener
        ->set('ekyna_commerce.listener.abstract_invoice_item', AbstractInvoiceItemListener::class)
            ->abstract()
            ->lazy()
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setContextProvider', [service('ekyna_commerce.provider.context')])
            ->call('setTaxResolver', [service('ekyna_commerce.resolver.tax')])
            ->call('setAuthorizationChecker', [service('security.authorization_checker')])

        // Invoice builder
        ->set('ekyna_commerce.builder.invoice', InvoiceBuilder::class)
            ->lazy()
            ->args([
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.factory.resolver.invoice_availability'),
                service('ekyna_commerce.calculator.invoice_subject'),
                service('ekyna_resource.provider.locale'),
                service('ekyna_commerce.transformer.array_address'),
                service('libphonenumber\PhoneNumberUtil'),
            ])

        // Invoice archiver
        ->set('ekyna_commerce.archiver.invoice', InvoiceArchiver::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.factory.factory'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_commerce.factory.document_renderer'),
                service('translator'),
            ])

        // Invoice payment resolver
        ->set('ekyna_commerce.resolver.invoice_payment', InvoicePaymentResolver::class)
            ->args([
                service('ekyna_commerce.converter.currency'),
            ])

        // Invoice subject state resolver
        ->set('ekyna_commerce.resolver.state.invoice_subject', InvoiceSubjectStateResolver::class)
            ->args([
                service('ekyna_commerce.calculator.invoice_subject'),
            ])

        // Invoice payment resolver
        ->set('ekyna_commerce.factory.resolver.invoice_availability', AvailabilityResolverFactory::class)
            ->args([
                service('ekyna_commerce.calculator.invoice_subject'),
                service('ekyna_commerce.calculator.shipment_subject'),
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::onClear,
                'connection' => 'default',
                'method'     => 'clear',
            ])
            ->tag('resource.event_listener', [
                'event'  => QueueEvents::QUEUE_CLOSE,
                'method' => 'clear',
            ])
            ->tag('resource.event_listener', [
                'event'  => QueueEvents::QUEUE_FLUSH,
                'method' => 'clear',
            ])
    ;
};
