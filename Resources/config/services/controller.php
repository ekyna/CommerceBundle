<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Controller\Admin;
use Ekyna\Bundle\CommerceBundle\Controller\Api;
use Ekyna\Bundle\CommerceBundle\Controller\Cart;
use Ekyna\Bundle\CommerceBundle\Controller\Payment\AcceptController;
use Ekyna\Bundle\CommerceBundle\Controller\Payment\HangController;
use Ekyna\Bundle\CommerceBundle\Controller\Payment\NotifyController;
use Ekyna\Bundle\CommerceBundle\Controller\Payment\RejectController;
use Ekyna\Bundle\CommerceBundle\Controller\Subject\AddToCartController;
use Ekyna\Bundle\CommerceBundle\Controller\Subject\ResupplyAlertController;
use Ekyna\Bundle\CommerceBundle\Controller\WidgetController;

return static function (ContainerConfigurator $container) {
    $container->import(__DIR__ . '/controller/account.php');

    $services = $container->services();

    // Admin customer address choice list controller
    $services->set(Admin\CustomerAddress\ChoiceListController::class)
        ->args([
            service('security.authorization_checker'),
            service('ekyna_resource.repository.factory'),
            service('serializer'),
        ])
        ->public();

    // Admin export accounting controller
    $services->set(Admin\Export\AccountingController::class)
        ->args([
            service('ekyna_commerce.helper.export_form'),
            service('ekyna_commerce.exporter.accounting'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->public();

    // Admin export invoice cost controller
    $services->set(Admin\Export\CostController::class)
        ->args([
            service('ekyna_commerce.helper.export_form'),
            service('ekyna_commerce.exporter.cost'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->public();

    // Admin export invoice controller
    $services->set('ekyna_commerce.controller.admin.export.invoice', Admin\Export\InvoiceController::class)
        ->args([
            service('ekyna_commerce.exporter.order_invoice'),
            service('router'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->alias(Admin\Export\InvoiceController::class, 'ekyna_commerce.controller.admin.export.invoice')->public();

    // Admin export order controller
    $services->set('ekyna_commerce.controller.admin.export.order', Admin\Export\OrderController::class)
        ->args([
            service('ekyna_commerce.exporter.order_list'),
            service('router'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->alias(Admin\Export\OrderController::class, 'ekyna_commerce.controller.admin.export.order')->public();

    // Admin export order item controller
    $services->set('ekyna_commerce.controller.admin.export.order_item', Admin\Export\OrderItemController::class)
        ->args([
            service('ekyna_commerce.helper.export_form'),
            service('ekyna_commerce.exporter.order_item'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->alias(Admin\Export\OrderItemController::class, 'ekyna_commerce.controller.admin.export.order_item')->public();

    // Admin export supplier order controller
    $services->set('ekyna_commerce.controller.admin.export.supplier_order', Admin\Export\SupplierOrderController::class)
        ->args([
            service('ekyna_commerce.exporter.supplier_order'),
            service('router'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->alias(Admin\Export\SupplierOrderController::class, 'ekyna_commerce.controller.admin.export.supplier_order')
        ->public();

    // Admin export supplier order item controller
    $services->set('ekyna_commerce.controller.admin.export.supplier_order_item', Admin\Export\SupplierOrderItemController::class)
        ->args([
            service('ekyna_commerce.exporter.supplier_order_item'),
            service('router'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->alias(Admin\Export\SupplierOrderItemController::class, 'ekyna_commerce.controller.admin.export.supplier_order_item')
        ->public();

    // Admin export stat controller
    $services->set('ekyna_commerce.controller.admin.export.stat', Admin\Export\StatController::class)
        ->args([
            service('ekyna_commerce.exporter.stat'),
            service('router'),
            service('ekyna_ui.helper.flash'),
            param('kernel.debug'),
        ])
        ->alias(Admin\Export\StatController::class, 'ekyna_commerce.controller.admin.export.stat')->public();

    // Admin order list abstract controller
    $services->set('ekyna_commerce.controller.admin.list.abstract', Admin\OrderList\AbstractListController::class)
        ->abstract()
        ->args([
            service('ekyna_resource.helper'),
            service('table.factory'),
            service('ekyna_admin.menu.builder'),
            service('twig'),
        ]);

    // Admin order list invoice controller
    $services->set('ekyna_commerce.controller.admin.list.invoice', Admin\OrderList\InvoiceListController::class)
        ->parent('ekyna_commerce.controller.admin.list.abstract')
        ->alias(Admin\OrderList\InvoiceListController::class, 'ekyna_commerce.controller.admin.list.invoice')->public();

    // Admin order list payment controller
    $services->set('ekyna_commerce.controller.admin.list.payment', Admin\OrderList\PaymentListController::class)
        ->parent('ekyna_commerce.controller.admin.list.abstract')
        ->alias(Admin\OrderList\PaymentListController::class, 'ekyna_commerce.controller.admin.list.payment')->public();

    // Admin order list shipment controller
    $services->set('ekyna_commerce.controller.admin.list.shipment', Admin\OrderList\ShipmentListController::class)
        ->parent('ekyna_commerce.controller.admin.list.abstract')
        ->alias(Admin\OrderList\ShipmentListController::class, 'ekyna_commerce.controller.admin.list.shipment')
        ->public();

    // Admin order document abstract controller
    $services->set('ekyna_commerce.controller.admin.document.abstract', Admin\OrderList\AbstractDocumentController::class)
        ->abstract()
        ->args([
            service('ekyna_resource.helper'),
            service('ekyna_resource.repository.factory'),
            service('ekyna_commerce.factory.document_renderer'),
            service('ekyna_ui.helper.flash'),
            service('router'),
        ]);

    // Admin order document invoice controller
    $services->set('ekyna_commerce.controller.admin.document.invoice', Admin\OrderList\InvoiceDocumentController::class)
        ->parent('ekyna_commerce.controller.admin.document.abstract')
        ->alias(Admin\OrderList\InvoiceDocumentController::class, 'ekyna_commerce.controller.admin.document.invoice')
        ->public();

    // Admin order document shipment controller
    $services->set('ekyna_commerce.controller.admin.document.shipment', Admin\OrderList\ShipmentDocumentController::class)
        ->parent('ekyna_commerce.controller.admin.document.abstract')
        ->alias(Admin\OrderList\ShipmentDocumentController::class, 'ekyna_commerce.controller.admin.document.shipment')
        ->public();

    // Admin inventory controller
    $services->set('ekyna_commerce.controller.admin.inventory', Admin\InventoryController::class)
        ->args([
            service('ekyna_commerce.registry.subject_provider'),
            service('twig'),
        ])
        ->alias(Admin\InventoryController::class, 'ekyna_commerce.controller.admin.inventory')->public();

    // Admin payment controller
    $services->set('ekyna_commerce.controller.admin.payment', Admin\PaymentStatusController::class)
        ->args([
            service('ekyna_commerce.helper.payment'),
            service('ekyna_resource.helper'),
        ])
        ->alias(Admin\PaymentStatusController::class, 'ekyna_commerce.controller.admin.payment')->public();

    // Admin shipment platform controller
    $services->set('ekyna_commerce.controller.admin.shipment_platform', Admin\ShipmentPlatformController::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->alias(Admin\ShipmentPlatformController::class, 'ekyna_commerce.controller.admin.shipment_platform')->public();

    // Admin shipment platform controller
    $services->set('ekyna_commerce.controller.admin.report', Admin\Report\ReportController::class)
        ->args([
            service('ekyna_commerce.repository.report_request'),
            service('doctrine'),
            service('ekyna_resource.provider.locale'),
            service('ekyna_admin.provider.user'),
            service('form.factory'),
            service('router'),
            service('twig'),
            service('messenger.bus.default'),
            service('ekyna_admin.menu.builder'),
            service('ekyna_ui.helper.flash'),
        ])
        ->alias(Admin\Report\ReportController::class, 'ekyna_commerce.controller.admin.report')->public();

    // Api customer controller
    $services->set('ekyna_commerce.controller.api.customer', Api\CustomerController::class)
        ->args([
            service('ekyna_commerce.repository.customer'),
            service('ekyna_commerce.filesystem'),
        ])
        ->alias(Api\CustomerController::class, 'ekyna_commerce.controller.api.customer')->public();

    // Api pricing controller
    $services->set('ekyna_commerce.controller.api.pricing', Api\PricingController::class)
        ->args([
            service('ekyna_commerce.api.pricing'),
            service('twig'),
        ])
        ->alias(Api\PricingController::class, 'ekyna_commerce.controller.api.pricing')->public();

    // Api shipment gateway controller
    $services->set('ekyna_commerce.controller.api.shipment_gateway', Api\ShipmentGatewayController::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
            service('ekyna_commerce.repository.relay_point'),
            service('ekyna_commerce.manager.relay_point'),
            service('serializer'),
        ])
        ->alias(Api\ShipmentGatewayController::class, 'ekyna_commerce.controller.api.shipment_gateway')->public();


    // Abstract cart controller
    // TODO Rework (too much deps)
    $services->set('ekyna_commerce.controller.abstract_cart', Cart\AbstractController::class)
        ->abstract()
        ->call('setFeatures', [service('ekyna_commerce.features')])
        ->call('setEnvironment', [service('twig')])
        ->call('setUrlGenerator', [service('router')])
        ->call('setTranslator', [service('translator')])
        ->call('setCartHelper', [service('ekyna_commerce.helper.cart')])
        ->call('setUserProvider', [service('ekyna_user.provider.user')])
        ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
        ->call('setStepValidator', [service('ekyna_commerce.validator.sale_step')]);

    // Cart controller
    $services->set('ekyna_commerce.controller.cart', Cart\CartController::class)
        ->parent('ekyna_commerce.controller.abstract_cart')
        ->args([
            service('ekyna_commerce.helper.coupon'),
            service('ekyna_ui.modal.renderer'),
            service('ekyna_commerce.filesystem'),
        ])
        ->alias(Cart\CartController::class, 'ekyna_commerce.controller.cart')->public();

    // Cart checkout controller
    $services->set('ekyna_commerce.controller.cart_checkout', Cart\CheckoutController::class)
        ->parent('ekyna_commerce.controller.abstract_cart')
        ->args([
            service('ekyna_commerce.repository.order'),
            service('ekyna_commerce.factory.quote'),
            service('ekyna_commerce.manager.payment_checkout'),
            service('ekyna_commerce.helper.payment'),
            service('ekyna_commerce.transformer.sale'),
            service('form.factory'),
            service('event_dispatcher'),
            service('ekyna_ui.helper.flash'),
        ])
        ->alias(Cart\CheckoutController::class, 'ekyna_commerce.controller.cart_checkout')->public();

    // Subject add to cart controller
    $services->set('ekyna_commerce.controller.subject.add_to_cart', AddToCartController::class)
        ->args([
            service('ekyna_ui.modal.renderer'),
            service('ekyna_commerce.helper.cart'),
            service('ekyna_commerce.helper.subject'),
        ])
        ->alias(AddToCartController::class, 'ekyna_commerce.controller.subject.add_to_cart')->public();

    // Subject resupply alert controller
    // TODO if feature enabled
    $services->set('ekyna_commerce.controller.subject.resupply_alert', ResupplyAlertController::class)
        ->args([
            service('ekyna_ui.modal.renderer'),
            service('ekyna_commerce.provider.customer'),
            service('ekyna_commerce.helper.resupply_alert'),
            service('twig'),
            service('ekyna_commerce.features'),
        ])
        ->alias(ResupplyAlertController::class, 'ekyna_commerce.controller.subject.resupply_alert')->public();

    // Widget controller
    $services->set('ekyna_commerce.controller.widget', WidgetController::class)
        ->args([
            service('ekyna_commerce.helper.widget'),
            service('ekyna_commerce.renderer.widget'),
            service('router'),
            param('ekyna_cms.home_route'),
        ])
        ->alias(WidgetController::class, 'ekyna_commerce.controller.widget')->public();


    // Payment controllers
    $services->set('ekyna_commerce.controller.payment.accept', AcceptController::class)
        ->args([
            service('payum'),
        ])
        ->alias(AcceptController::class, 'ekyna_commerce.controller.payment.accept')->public();

    $services->set('ekyna_commerce.controller.payment.hang', HangController::class)
        ->args([
            service('payum'),
        ])
        ->alias(HangController::class, 'ekyna_commerce.controller.payment.hang')->public();

    $services->set('ekyna_commerce.controller.payment.notify', NotifyController::class)
        ->args([
            service('ekyna_commerce.helper.payment'),
        ])
        ->alias(NotifyController::class, 'ekyna_commerce.controller.payment.notify')->public();

    $services->set('ekyna_commerce.controller.payment.reject', RejectController::class)
        ->args([
            service('payum'),
        ])
        ->alias(RejectController::class, 'ekyna_commerce.controller.payment.reject')->public();
};
