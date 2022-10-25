<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\MessageHandler\SendSalesReportHandler;
use Ekyna\Bundle\CommerceBundle\Repository\ReportRequestRepository;
use Ekyna\Bundle\CommerceBundle\Service\Report\ReportMailer;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\ReportRegistryPass;
use Ekyna\Component\Commerce\Report\Fetcher\InvoiceFetcher;
use Ekyna\Component\Commerce\Report\Fetcher\OrderFetcher;
use Ekyna\Component\Commerce\Report\Fetcher\SupplierOrderFetcher;
use Ekyna\Component\Commerce\Report\ReportGenerator;
use Ekyna\Component\Commerce\Report\ReportRegistry;
use Ekyna\Component\Commerce\Report\Section\CustomerGroupsSection;
use Ekyna\Component\Commerce\Report\Section\CustomersSection;
use Ekyna\Component\Commerce\Report\Section\InvoicesSection;
use Ekyna\Component\Commerce\Report\Section\OrdersSection;
use Ekyna\Component\Commerce\Report\Section\SupplierOrdersSection;
use Ekyna\Component\Commerce\Report\Util\OrderUtil;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Report request repository
        ->set('ekyna_commerce.repository.report_request', ReportRequestRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->tag('doctrine.repository_service')

        // Invoice fetcher
        ->set('ekyna_commerce.report.fetcher.invoice', InvoiceFetcher::class)
            ->args([
                service('ekyna_commerce.repository.order_invoice'),
                service('ekyna_commerce.manager.order_invoice'),
            ])
            ->tag(ReportRegistryPass::FETCHER_TAG)

        // Order fetcher
        ->set('ekyna_commerce.report.fetcher.order', OrderFetcher::class)
            ->args([
                service('ekyna_commerce.repository.order'),
                service('ekyna_commerce.manager.order'),
                service('ekyna_commerce.report.util.order'),
            ])
            ->tag(ReportRegistryPass::FETCHER_TAG)

        // Supplier order fetcher
        ->set('ekyna_commerce.report.fetcher.supplier_order', SupplierOrderFetcher::class)
            ->args([
                service('ekyna_commerce.repository.supplier_order'),
                service('ekyna_commerce.manager.supplier_order'),
            ])
            ->tag(ReportRegistryPass::FETCHER_TAG)

        // Customer groups section
        ->set('ekyna_commerce.report.section.customer_groups', CustomerGroupsSection::class)
            ->args([
                service('ekyna_commerce.report.util.order'),
            ])
            ->tag(ReportRegistryPass::SECTION_TAG)

        // Customers section
        ->set('ekyna_commerce.report.section.customers', CustomersSection::class)
            ->args([
                service('ekyna_commerce.report.util.order'),
            ])
            ->tag(ReportRegistryPass::SECTION_TAG)

        // Invoices section
        ->set('ekyna_commerce.report.section.order_invoicess', InvoicesSection::class)
            ->tag(ReportRegistryPass::SECTION_TAG)

        // Orders section
        ->set('ekyna_commerce.report.section.orders', OrdersSection::class)
            ->args([
                service('ekyna_commerce.report.util.order'),
            ])
            ->tag(ReportRegistryPass::SECTION_TAG)

        // Supplier orders section
        ->set('ekyna_commerce.report.section.supplier_order', SupplierOrdersSection::class)
            ->args([
                service('ekyna_commerce.helper.stock_subject_quantity'),
                service('ekyna_commerce.report.util.order'),
            ])
            ->tag(ReportRegistryPass::SECTION_TAG)

        // Order margin util
        ->set('ekyna_commerce.report.util.order', OrderUtil::class)
            ->args([
                service('ekyna_commerce.factory.margin_calculator'),
                param('ekyna_commerce.default.currency'),
            ])

        // XLS writer
        ->set('ekyna_commerce.report.writer.xls', XlsWriter::class)
            ->tag(ReportRegistryPass::WRITER_TAG)

        // Registry
        ->set('ekyna_commerce.report.registry', ReportRegistry::class)

        // Generator
        ->set('ekyna_commerce.report.generator', ReportGenerator::class)
            ->args([
                service('ekyna_commerce.report.registry'),
            ])

        // Mailer
        ->set('ekyna_commerce.report.mailer', ReportMailer::class)
            ->args([
                service('ekyna_commerce.report.generator'),
                service('ekyna_commerce.report.registry'),
                service('ekyna_setting.manager'),
                service('ekyna_commerce.factory.formatter'),
                service('translator'),
                service('twig'),
                service('mailer.mailer'),
            ])

        // Message handler
        ->set('ekyna_commerce.message_handler.report', SendSalesReportHandler::class)
            ->args([
                service('ekyna_commerce.report.mailer'),
            ])
            ->tag('messenger.message_handler')
    ;
};
