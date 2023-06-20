<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\MessageHandler\SendSalesReportHandler;
use Ekyna\Component\Commerce\Order\MessageHandler\UpdateOrderMarginHandler;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Send sales report handler
    $services
        ->set('ekyna_commerce.message_handler.send_sales_report', SendSalesReportHandler::class)
        ->args([
            service('ekyna_commerce.report.mailer'),
        ])
        ->tag('messenger.message_handler');

    // Update order margin handler
    $services
        ->set('ekyna_commerce.message_handler.update_order_margin', UpdateOrderMarginHandler::class)
        ->args([
            service('ekyna_commerce.repository.order'),
            service('ekyna_commerce.updater.order'),
            service('ekyna_commerce.factory.invoice_margin_calculator'),
            service('ekyna_commerce.manager.order'),
        ])
        ->tag('messenger.message_handler');
};
