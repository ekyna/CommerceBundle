<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Dashboard\DebtWidget;
use Ekyna\Bundle\CommerceBundle\Dashboard\ExportWidget;
use Ekyna\Bundle\CommerceBundle\Dashboard\StatWidget;
use Ekyna\Bundle\CommerceBundle\Dashboard\StockWidget;
use Ekyna\Bundle\CommerceBundle\Service\Stat\InvoiceStatCalculator;
use Ekyna\Bundle\CommerceBundle\Service\Stat\OrderStatCalculator;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StatUpdater;
use Ekyna\Bundle\CommerceBundle\Service\Stat\StockStatUpdater;
use Ekyna\Component\Commerce\Stat\Entity\InvoiceStat;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\StatHelper;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Stat helper
    $services
        ->set('ekyna_commerce.helper.stat', StatHelper::class);

    // Order Stat calculator
    $services
        ->set('ekyna_commerce.calculator.stat.order', OrderStatCalculator::class)
        ->args([
            service('ekyna_commerce.helper.stat'),
            service('doctrine'),
            param('ekyna_commerce.class.order'),
        ]);

    // Invoice Stat calculator
    $services
        ->set('ekyna_commerce.calculator.stat.invoice', InvoiceStatCalculator::class)
        ->args([
            service('ekyna_commerce.helper.stat'),
            service('doctrine'),
            param('ekyna_commerce.class.order_invoice'),
        ]);

    // Stock stat updater
    $services
        ->set('ekyna_commerce.updater.stat.stock', StockStatUpdater::class)
        ->args([
            service('doctrine'),
        ]);

    // Order Stat updater
    $services
        ->set('ekyna_commerce.updater.stat.order', StatUpdater::class)
        ->args([
            service('ekyna_commerce.calculator.stat.order'),
            service('ekyna_commerce.helper.stat'),
            service('doctrine'),
            OrderStat::class,
        ]);

    // Invoice Stat updater
    $services
        ->set('ekyna_commerce.updater.stat.invoice', StatUpdater::class)
        ->args([
            service('ekyna_commerce.calculator.stat.invoice'),
            service('ekyna_commerce.helper.stat'),
            service('doctrine'),
            InvoiceStat::class,
        ]);

    // Dashboard stat widget
    $services
        ->set('ekyna_commerce.dashboard.stat_widget', StatWidget::class)
        ->args([
            service('doctrine'),
            service('ekyna_commerce.helper.stat'),
            service('security.authorization_checker'),
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
