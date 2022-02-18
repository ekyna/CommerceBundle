<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Controller\Account;
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
use Ekyna\Bundle\CommerceBundle\EventListener\AccountControllerListener;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Account controller listener
        ->set('ekyna_commerce.listener.account_controller', AccountControllerListener::class)
            ->args([
                service('ekyna_user.provider.user'),
                service('ekyna_commerce.provider.customer'),
                service('router'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onController'])

        // Account contact controller
        ->set('ekyna_commerce.controller.account.contact', Account\ContactController::class)
            ->args([
                service('ekyna_commerce.features'),
                service('ekyna_commerce.repository.customer_contact'),
                service('ekyna_commerce.factory.customer_contact'),
                service('ekyna_commerce.manager.customer_contact'),
                service('ekyna_ui.helper.flash'),
                service('form.factory'),
                service('router'),
                service('twig'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\ContactController::class, 'ekyna_commerce.controller.account.contact')->public()

        // Account invoice controller
        ->set('ekyna_commerce.controller.account.invoice', Account\InvoiceController::class)
            ->args([
                service('ekyna_commerce.repository.order_invoice'),
                service('ekyna_commerce.factory.document_renderer'),
                service('ekyna_ui.helper.flash'),
                service('router'),
                service('twig'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\InvoiceController::class, 'ekyna_commerce.controller.account.invoice')->public()

        // Account loyalty controller
        ->set('ekyna_commerce.controller.account.loyalty', Account\LoyaltyController::class)
            ->args([
                service('twig'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\LoyaltyController::class, 'ekyna_commerce.controller.account.loyalty')->public()

        // Account payment controller
        ->set('ekyna_commerce.controller.account.address', Account\AddressController::class)
            ->args([
                service('ekyna_commerce.repository.customer_address'),
                service('ekyna_commerce.factory.customer_address'),
                service('ekyna_commerce.manager.customer_address'),
                service('twig'),
                service('form.factory'),
                service('ekyna_ui.helper.flash'),
                service('router'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\AddressController::class, 'ekyna_commerce.controller.account.address')->public()

        // Account order controller
        ->set('ekyna_commerce.controller.account.order', Account\OrderController::class)
            ->args([
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.manager.factory'),
                service('router'),
                service('twig'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.validator.sale_step'),
                service('ekyna_commerce.manager.payment_checkout'),
                service('ekyna_commerce.helper.payment'),
                service('form.factory'),
                service('ekyna_ui.helper.flash'),
                service('ekyna_commerce.exporter.sale_csv'),
                service('ekyna_commerce.exporter.sale_xls'),
                service('ekyna_commerce.factory.document_renderer'),
                service('ekyna_commerce.filesystem'),
                param('kernel.debug'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\OrderController::class, 'ekyna_commerce.controller.account.order')->public()

        // Account payment controller
        ->set('ekyna_commerce.controller.account.payment', Account\PaymentController::class)
            ->args([
                service('ekyna_commerce.helper.payment'),
                service('router'),
            ])
            ->alias(Account\PaymentController::class, 'ekyna_commerce.controller.account.payment')->public()

        // Account quote controller
        ->set('ekyna_commerce.controller.account.quote', Account\QuoteController::class)
            ->args([
                // Quote trait
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_commerce.helper.sale_view'),
                service('router'),
                service('twig'),
                // Controller
                service('ekyna_commerce.updater.sale'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.validator.sale_step'),
                service('ekyna_commerce.manager.payment_checkout'),
                service('ekyna_commerce.helper.payment'),
                service('translator'),
                service('form.factory'),
                service('ekyna_ui.helper.flash'),
                service('ekyna_commerce.exporter.sale_csv'),
                service('ekyna_commerce.exporter.sale_xls'),
                service('ekyna_commerce.filesystem'),
                service('ekyna_ui.modal.renderer'),
                param('kernel.debug'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\QuoteController::class, 'ekyna_commerce.controller.account.quote')->public()

        // Account quote item controller
        ->set('ekyna_commerce.controller.account.quote_item', Account\QuoteItemController::class)
            ->args([
                // Quote trait
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_commerce.helper.sale_view'),
                service('router'),
                service('twig'),
                // Controller
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.form_flow.sale_item_add'),
                service('form.factory'),
                service('event_dispatcher'),
                service('ekyna_commerce.helper.sale'),
                service('ekyna_ui.helper.flash'),
                service('ekyna_ui.modal.renderer'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(Account\QuoteItemController::class, 'ekyna_commerce.controller.account.quote_item')->public()
    ;

    $container
        ->services()

        // Admin customer address choice list controller
        ->set('ekyna_commerce.controller.admin.customer_address_choice_list', Admin\CustomerAddress\ChoiceListController::class)
            ->args([
                service('security.authorization_checker'),
                service('ekyna_resource.repository.factory'),
                service('serializer'),
            ])
            ->alias(
                Admin\CustomerAddress\ChoiceListController::class,
                'ekyna_commerce.controller.admin.customer_address_choice_list'
            )
            ->public()

        // Admin export accounting controller
        ->set('ekyna_commerce.controller.admin.export.accounting', Admin\Export\AccountingController::class)
            ->args([
                service('ekyna_commerce.exporter.accounting'),
                service('form.factory'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\AccountingController::class, 'ekyna_commerce.controller.admin.export.accounting')->public()

        // Admin export invoice cost controller
        ->set('ekyna_commerce.controller.admin.export.cost', Admin\Export\CostController::class)
            ->args([
                service('ekyna_commerce.exporter.cost'),
                service('form.factory'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\CostController::class, 'ekyna_commerce.controller.admin.export.cost')->public()

        // Admin export invoice controller
        ->set('ekyna_commerce.controller.admin.export.invoice', Admin\Export\InvoiceController::class)
            ->args([
                service('ekyna_commerce.exporter.order_invoice'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\InvoiceController::class, 'ekyna_commerce.controller.admin.export.invoice')->public()

        // Admin export order controller
        ->set('ekyna_commerce.controller.admin.export.order', Admin\Export\OrderController::class)
            ->args([
                service('ekyna_commerce.exporter.order_list'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\OrderController::class, 'ekyna_commerce.controller.admin.export.order')->public()

        // Admin export supplier order controller
        ->set('ekyna_commerce.controller.admin.export.supplier_order', Admin\Export\SupplierOrderController::class)
            ->args([
                service('ekyna_commerce.exporter.supplier_order'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\SupplierOrderController::class, 'ekyna_commerce.controller.admin.export.supplier_order')->public()

        // Admin export supplier order item controller
        ->set('ekyna_commerce.controller.admin.export.supplier_order_item', Admin\Export\SupplierOrderItemController::class)
            ->args([
                service('ekyna_commerce.exporter.supplier_order_item'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\SupplierOrderItemController::class, 'ekyna_commerce.controller.admin.export.supplier_order_item')->public()

        // Admin export stat controller
        ->set('ekyna_commerce.controller.admin.export.stat', Admin\Export\StatController::class)
            ->args([
                service('ekyna_commerce.exporter.stat'),
                service('router'),
                service('ekyna_ui.helper.flash'),
                param('kernel.debug'),
            ])
            ->alias(Admin\Export\StatController::class, 'ekyna_commerce.controller.admin.export.stat')->public()

        // Admin order list abstract controller
        ->set('ekyna_commerce.controller.admin.list.abstract', Admin\OrderList\AbstractListController::class)
            ->abstract()
            ->args([
                service('ekyna_resource.helper'),
                service('table.factory'),
                service('ekyna_admin.menu.builder'),
                service('twig'),
            ])

        // Admin order list invoice controller
        ->set('ekyna_commerce.controller.admin.list.invoice', Admin\OrderList\InvoiceListController::class)
            ->parent('ekyna_commerce.controller.admin.list.abstract')
            ->alias(Admin\OrderList\InvoiceListController::class, 'ekyna_commerce.controller.admin.list.invoice')->public()

        // Admin order list payment controller
        ->set('ekyna_commerce.controller.admin.list.payment', Admin\OrderList\PaymentListController::class)
            ->parent('ekyna_commerce.controller.admin.list.abstract')
            ->alias(Admin\OrderList\PaymentListController::class, 'ekyna_commerce.controller.admin.list.payment')->public()

        // Admin order list shipment controller
        ->set('ekyna_commerce.controller.admin.list.shipment', Admin\OrderList\ShipmentListController::class)
            ->parent('ekyna_commerce.controller.admin.list.abstract')
            ->alias(Admin\OrderList\ShipmentListController::class, 'ekyna_commerce.controller.admin.list.shipment')->public()

        // Admin order document abstract controller
        ->set('ekyna_commerce.controller.admin.document.abstract', Admin\OrderList\AbstractDocumentController::class)
            ->abstract()
            ->args([
                service('ekyna_resource.helper'),
                service('ekyna_resource.repository.factory'),
                service('ekyna_commerce.factory.document_renderer'),
                service('ekyna_ui.helper.flash'),
                service('router'),
            ])

        // Admin order document invoice controller
        ->set('ekyna_commerce.controller.admin.document.invoice', Admin\OrderList\InvoiceDocumentController::class)
            ->parent('ekyna_commerce.controller.admin.document.abstract')
            ->alias(Admin\OrderList\InvoiceDocumentController::class, 'ekyna_commerce.controller.admin.document.invoice')->public()

        // Admin order document shipment controller
        ->set('ekyna_commerce.controller.admin.document.shipment', Admin\OrderList\ShipmentDocumentController::class)
            ->parent('ekyna_commerce.controller.admin.document.abstract')
            ->alias(Admin\OrderList\ShipmentDocumentController::class, 'ekyna_commerce.controller.admin.document.shipment')->public()

        // Admin inventory controller
        ->set('ekyna_commerce.controller.admin.inventory', Admin\InventoryController::class)
            ->args([
                service('ekyna_commerce.registry.subject_provider'),
                service('twig'),
            ])
            ->alias(Admin\InventoryController::class, 'ekyna_commerce.controller.admin.inventory')->public()

        // Admin payment controller
        ->set('ekyna_commerce.controller.admin.payment', Admin\PaymentStatusController::class)
            ->args([
                service('ekyna_commerce.helper.payment'),
                service('ekyna_resource.helper'),
            ])
            ->alias(Admin\PaymentStatusController::class, 'ekyna_commerce.controller.admin.payment')->public()

        // Admin shipment platform controller
        ->set('ekyna_commerce.controller.admin.shipment_platform', Admin\ShipmentPlatformController::class)
            ->args([
                service('ekyna_commerce.registry.shipment_gateway'),
            ])
            ->alias(Admin\ShipmentPlatformController::class, 'ekyna_commerce.controller.admin.shipment_platform')->public()

        // Api customer controller
        ->set('ekyna_commerce.controller.api.customer', Api\CustomerController::class)
            ->args([
                service('ekyna_commerce.repository.customer'),
                service('ekyna_commerce.filesystem'),
            ])
            ->alias(Api\CustomerController::class, 'ekyna_commerce.controller.api.customer')->public()

        // Api pricing controller
        ->set('ekyna_commerce.controller.api.pricing', Api\PricingController::class)
            ->args([
                service('ekyna_commerce.api.pricing'),
                service('twig'),
            ])
            ->alias(Api\PricingController::class, 'ekyna_commerce.controller.api.pricing')->public()

        // Api shipment gateway controller
        ->set('ekyna_commerce.controller.api.shipment_gateway', Api\ShipmentGatewayController::class)
            ->args([
                service('ekyna_commerce.registry.shipment_gateway'),
                service('ekyna_commerce.repository.relay_point'),
                service('ekyna_commerce.manager.relay_point'),
                service('serializer'),
            ])
            ->alias(Api\ShipmentGatewayController::class, 'ekyna_commerce.controller.api.shipment_gateway')->public()
    ;

    $container
        ->services()
        // Abstract cart controller
        // TODO Rework (too much deps)
        ->set('ekyna_commerce.controller.abstract_cart', Cart\AbstractController::class)
            ->abstract()
            ->call('setFeatures', [service('ekyna_commerce.features')])
            ->call('setEnvironment', [service('twig')])
            ->call('setUrlGenerator', [service('router')])
            ->call('setTranslator', [service('translator')])
            ->call('setCartHelper', [service('ekyna_commerce.helper.cart')])
            ->call('setUserProvider', [service('ekyna_user.provider.user')])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->call('setStepValidator', [service('ekyna_commerce.validator.sale_step')])

        // Cart controller
        ->set('ekyna_commerce.controller.cart', Cart\CartController::class)
            ->parent('ekyna_commerce.controller.abstract_cart')
            ->args([
                service('ekyna_commerce.helper.coupon'),
                service('ekyna_ui.modal.renderer'),
                service('ekyna_commerce.filesystem'),
            ])
            ->alias(Cart\CartController::class, 'ekyna_commerce.controller.cart')->public()

        // Cart checkout controller
        ->set('ekyna_commerce.controller.cart_checkout', Cart\CheckoutController::class)
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
            ->alias(Cart\CheckoutController::class, 'ekyna_commerce.controller.cart_checkout')->public()

        // Subject add to cart controller
        ->set('ekyna_commerce.controller.subject.add_to_cart', AddToCartController::class)
            ->args([
                service('ekyna_ui.modal.renderer'),
                service('ekyna_commerce.helper.cart'),
            ])
            ->alias(AddToCartController::class, 'ekyna_commerce.controller.subject.add_to_cart')->public()

        // Subject resupply alert controller
        // TODO if feature enabled
        ->set('ekyna_commerce.controller.subject.resupply_alert', ResupplyAlertController::class)
            ->args([
                service('ekyna_ui.modal.renderer'),
                service('ekyna_commerce.provider.customer'),
                service('ekyna_commerce.helper.resupply_alert'),
                service('twig'),
                service('ekyna_commerce.features'),
            ])
            ->alias(ResupplyAlertController::class, 'ekyna_commerce.controller.subject.resupply_alert')->public()

        // Widget controller
        ->set('ekyna_commerce.controller.widget', WidgetController::class)
            ->args([
                service('ekyna_commerce.helper.widget'),
                service('ekyna_commerce.renderer.widget'),
                service('router'),
                param('ekyna_cms.home_route'),
            ])
            ->alias(WidgetController::class, 'ekyna_commerce.controller.widget')->public()
    ;

    $container
        ->services()

        // Payment controllers
        ->set('ekyna_commerce.controller.payment.accept', AcceptController::class)
            ->args([
                service('payum'),
            ])
            ->alias(AcceptController::class, 'ekyna_commerce.controller.payment.accept')->public()
        ->set('ekyna_commerce.controller.payment.hang', HangController::class)
            ->args([
                service('payum'),
            ])
            ->alias(HangController::class, 'ekyna_commerce.controller.payment.hang')->public()
        ->set('ekyna_commerce.controller.payment.notify', NotifyController::class)
            ->args([
                service('ekyna_commerce.helper.payment'),
            ])
            ->alias(NotifyController::class, 'ekyna_commerce.controller.payment.notify')->public()
        ->set('ekyna_commerce.controller.payment.reject', RejectController::class)
            ->args([
                service('payum'),
            ])
            ->alias(RejectController::class, 'ekyna_commerce.controller.payment.reject')->public()
    ;
};
