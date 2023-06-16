<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\AccountingEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Service\Accounting\FactorAccountingFilter;
use Ekyna\Component\Commerce\Accounting\Export\AccountingExporter;
use Ekyna\Component\Commerce\Accounting\Export\CostExporter;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Accounting exporter
        ->set('ekyna_commerce.exporter.accounting', AccountingExporter::class)
            ->args([
                service('ekyna_commerce.repository.order_invoice'),
                service('ekyna_commerce.repository.order_payment'),
                service('ekyna_commerce.repository.accounting'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.factory.amount_calculator'),
                service('ekyna_commerce.calculator.invoice'),
                service('ekyna_commerce.resolver.invoice_payment'),
                service('ekyna_commerce.resolver.tax'),
                abstract_arg('Accounting exporter configuration'),
            ])
            ->call('addFilter', [inline_service(FactorAccountingFilter::class)]) // TODO Remove ?

        // Cost exporter
        ->set('ekyna_commerce.exporter.cost', CostExporter::class)
            ->args([
                service('ekyna_commerce.repository.order_invoice'),
                service('ekyna_commerce.repository.stock_adjustment'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.calculator.invoice'),
                service('ekyna_commerce.calculator.invoice_cost'),
                param('kernel.debug'),
            ])

        // Accounting event listener
        ->set('ekyna_commerce.accounting.event_subscriber', AccountingEventSubscriber::class)
            ->args([
                service('translator'),
            ])
            ->tag('resource.event_subscriber')
    ;
};
