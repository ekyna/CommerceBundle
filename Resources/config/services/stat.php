<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Dashboard\DebtWidget;
use Ekyna\Bundle\CommerceBundle\Dashboard\ExportWidget;
use Ekyna\Bundle\CommerceBundle\Dashboard\StatWidget;
use Ekyna\Bundle\CommerceBundle\Dashboard\StockWidget;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StatCalculator;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StatExporter;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StatUpdater;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Stat calculator
    $services
        ->set('ekyna_commerce.calculator.stat', StatCalculator::class)
        ->args([
            service('doctrine'),
            service('ekyna_commerce.factory.amount_calculator'),
            service('ekyna_commerce.factory.margin_calculator'),
            param('ekyna_commerce.class.order'),
            param('ekyna_commerce.default.currency'),
        ]);

    // Stat exporter
    $services
        ->set('ekyna_commerce.exporter.stat', StatExporter::class)
        ->args([
            service('ekyna_commerce.provider.region'),
            service('ekyna_commerce.calculator.stat'),
        ]);

    // Stat updater
    $services
        ->set('ekyna_commerce.updater.stat', StatUpdater::class)
        ->args([
            service('ekyna_commerce.calculator.stat'),
            service('doctrine'),
        ]);

    // Dashboard stat widget
    $services
        ->set('ekyna_commerce.dashboard.stat_widget', StatWidget::class)
        ->args([
            service('doctrine'),
        ])
        ->tag('ekyna_admin.dashboard_widget');

    // Dashboard stock widget
    $services
        ->set('ekyna_commerce.dashboard.stock_widget', StockWidget::class)
        ->args([
            service('doctrine'),
        ])
        ->tag('ekyna_admin.dashboard_widget');

    // Dashboard debt widget
    $services
        ->set('ekyna_commerce.dashboard.debt_widget', DebtWidget::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.repository.order'),
            service('ekyna_commerce.repository.supplier_order'),
        ])
        ->tag('ekyna_admin.dashboard_widget');

    // Dashboard export widget
    $services
        ->set('ekyna_commerce.dashboard.export_widget', ExportWidget::class)
        ->args([
            service('ekyna_commerce.helper.export_form'),
        ])
        ->tag('ekyna_admin.dashboard_widget');
};
