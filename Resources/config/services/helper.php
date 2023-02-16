<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Export\ExportFormHelper;
use Ekyna\Bundle\CommerceBundle\Service\Invoice\InvoiceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentRenderer;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentLabelRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Stock\AvailabilityHelper;
use Ekyna\Bundle\CommerceBundle\Service\Stock\ResupplyAlertHelper;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierHelper;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetRenderer;
use Ekyna\Component\Commerce\Stock\Helper\AdjustHelper;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Commerce\Stock\Helper\StockSubjectQuantityHelper;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Widget helper
    $services
        ->set('ekyna_commerce.helper.widget', WidgetHelper::class)
        ->args([
            service('ekyna_user.provider.user'),
            service('ekyna_commerce.provider.context'),
            service('ekyna_commerce.renderer.currency'),
            service('router'),
            service('request_stack'),
            service('form.factory'),
            service('translator'),
            param('ekyna_resource.locales'), // TODO ekyna_cms.public_locales (need to define)
            abstract_arg('Widget helper configuration'),
        ]);

    // Widget renderer
    $services
        ->set('ekyna_commerce.renderer.widget', WidgetRenderer::class)
        ->args([
            service('ekyna_commerce.helper.widget'),
            service('twig'),
            abstract_arg('Widget renderer configuration'),
        ])
        ->tag('twig.runtime');

    // Subject helper
    $services
        ->set('ekyna_commerce.helper.subject', SubjectHelper::class)
        ->args([
            service('ekyna_commerce.registry.subject_provider'),
            service('ekyna_commerce.features'),
            service('ekyna_resource.helper'),
            service('form.factory'),
            service('translator'),
        ])
        ->tag('twig.runtime')
        ->alias(SubjectHelperInterface::class, 'ekyna_commerce.helper.subject');

    // Export form helper
    $services
        ->set('ekyna_commerce.helper.export_form', ExportFormHelper::class)
        ->args([
            service('form.factory'),
            service('router'),
        ]);

    // Adjust helper
    $services
        ->set('ekyna_commerce.helper.adjust', AdjustHelper::class)
        ->args([
            service('ekyna_resource.factory.factory'),
            service('ekyna_resource.repository.factory'),
            service('ekyna_resource.manager.factory'),
        ]);

    // Availability helper
    $services
        ->set('ekyna_commerce.helper.availability', AvailabilityHelper::class)
        ->args([
            service('ekyna_commerce.factory.formatter'),
            service('translator'),
            100,
        ])
        ->tag('twig.runtime')
        ->alias(AvailabilityHelperInterface::class, 'ekyna_commerce.helper.availability');

    // Resupply alert helper
    $services
        ->set('ekyna_commerce.helper.resupply_alert', ResupplyAlertHelper::class)
        ->args([
            service('ekyna_commerce.repository.resupply_alert'),
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.helper.subject'),
            service('form.factory'),
            service('router'),
        ]);

    // Stock subject quantity helper
    $services
        ->set('ekyna_commerce.helper.stock_subject_quantity', StockSubjectQuantityHelper::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.calculator.invoice_subject'),
        ]);

    // Constants helper
    $services
        ->set('ekyna_commerce.helper.constants', ConstantsHelper::class)
        ->args([
            service('translator'),
            param('ekyna_commerce.class.genders'),
        ])
        ->tag('twig.runtime');

    // Sale helper
    $services
        ->set('ekyna_commerce.helper.sale', SaleHelper::class)
        ->args([
            service('ekyna_commerce.helper.factory'),
            service('ekyna_commerce.updater.sale'),
            service('form.factory'),
        ])
        ->alias(SaleHelper::class, 'ekyna_commerce.helper.sale');

    // Sale item helper
    $services
        ->set('ekyna_commerce.helper.sale_item', SaleItemHelper::class)
        ->args([
            service('event_dispatcher'),
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.calculator.shipment_subject'),
            service('ekyna_commerce.calculator.invoice_subject'),
        ]);

    // Sale view helper
    $services
        ->set('ekyna_commerce.helper.sale_view', SaleViewHelper::class)
        ->args([
            service('ekyna_commerce.builder.view'),
            service('ekyna_resource.helper'),
            service('form.factory'),
        ])
        ->tag('twig.runtime')
        ->alias(SaleViewHelper::class, 'ekyna_commerce.helper.sale_view');

    // Sale renderer
    $services
        ->set('ekyna_commerce.renderer.sale', SaleRenderer::class)
        ->args([
            service('ekyna_commerce.provider.context'),
            service('ekyna_commerce.builder.view'),
            service('twig'),
            service('ekyna_ui.renderer'),
            service('ekyna_resource.helper'),
        ])
        ->tag('twig.runtime');

    // Cart helper
    $services
        ->set('ekyna_commerce.helper.cart', CartHelper::class)
        ->args([
            service('ekyna_commerce.helper.sale'),
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.helper.sale_view'),
            service('ekyna_commerce.helper.factory'),
            service('ekyna_commerce.provider.cart'),
            service('ekyna_ui.modal.renderer'),
            service('event_dispatcher'),
            param('kernel.debug'),
        ])
        ->alias(CartHelper::class, 'ekyna_commerce.helper.cart');

    // Checkout renderer
    $services
        ->set('ekyna_commerce.renderer.checkout', CheckoutRenderer::class)
        ->args([
            service('event_dispatcher'),
        ])
        ->tag('twig.runtime');

    // Invoice helper
    $services
        ->set('ekyna_commerce.helper.invoice', InvoiceHelper::class)
        ->args([
            service('ekyna_commerce.resolver.due_date'),
            service('ekyna_commerce.resolver.invoice_payment'),
        ])
        ->tag('twig.runtime');

    // Payment helper and renderer
    $services
        ->set('ekyna_commerce.helper.payment', PaymentHelper::class)
        ->lazy(true)
        ->args([
            service('payum'),
            service('ekyna_commerce.checker.locking'),
            service('event_dispatcher'),
            service('ekyna_commerce.cache'),
            param('kernel.debug'),
        ]);

    $services
        ->set('ekyna_commerce.renderer.payment', PaymentRenderer::class)
        ->args([
            service('ekyna_commerce.calculator.payment'),
            service('ekyna_commerce.helper.payment'),
            service('ekyna_resource.helper'),
            service('translator'),
        ])
        ->tag('twig.runtime');

    // Shipment helper and renderer
    $services
        ->set('ekyna_commerce.helper.shipment', ShipmentHelper::class)
        ->lazy(true)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->call('setWeightCalculator', [service('ekyna_commerce.calculator.shipment_weight')])
        ->call('setAddressResolver', [service('ekyna_commerce.resolver.shipment_address')])
        ->tag('twig.runtime');
    $services
        ->set('ekyna_commerce.renderer.shipment', ShipmentRenderer::class)
        ->args([
            service('ekyna_commerce.builder.shipment_price_list'),
            service('ekyna_commerce.helper.shipment'),
            service('twig'),
            abstract_arg('Shipment price list template'),
        ])
        ->tag('twig.runtime');

    // Shipment label renderer
    $services
        ->set('ekyna_commerce.renderer.shipment_label', ShipmentLabelRenderer::class)
        ->lazy(true)
        ->args([
            service('twig'),
            service('ekyna_resource.generator.pdf'),
            service('ekyna_setting.manager'),
        ]);

    // Supplier helper
    $services
        ->set('ekyna_commerce.helper.supplier', SupplierHelper::class)
        ->args([
            service('ekyna_commerce.calculator.supplier_order'),
        ])
        ->tag('twig.runtime');
};
