<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Controller\Account;
use Ekyna\Bundle\CommerceBundle\EventListener\AccountControllerListener;
use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Symfony\Component\HttpKernel\KernelEvents;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Account controller listener
    $services
        ->set('ekyna_commerce.listener.account_controller', AccountControllerListener::class)
        ->args([
            service('ekyna_user.provider.user'),
            service('ekyna_commerce.provider.customer'),
            service('router'),
        ])
        ->tag('kernel.event_listener', [
            'event'  => KernelEvents::CONTROLLER,
            'method' => 'onController',
        ]);

    // Account contact controller
    $services
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
        ->alias(Account\ContactController::class, 'ekyna_commerce.controller.account.contact')->public();

    // Account invoice controller
    $services
        ->set('ekyna_commerce.controller.account.invoice', Account\InvoiceController::class)
        ->args([
            service('ekyna_commerce.repository.order_invoice'),
            service('ekyna_commerce.factory.document_renderer'),
            service('ekyna_ui.helper.flash'),
            service('router'),
            service('twig'),
        ])
        ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
        ->alias(Account\InvoiceController::class, 'ekyna_commerce.controller.account.invoice')->public();

    // Account loyalty controller
    $services
        ->set('ekyna_commerce.controller.account.loyalty', Account\LoyaltyController::class)
        ->args([
            service('twig'),
        ])
        ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
        ->alias(Account\LoyaltyController::class, 'ekyna_commerce.controller.account.loyalty')->public();

    // Account address controller
    $services
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
        ->alias(Account\AddressController::class, 'ekyna_commerce.controller.account.address')->public();


    // ---------------------------- Order ----------------------------

    // Account order resource helper
    $services
        ->set('ekyna_commerce.account.order_resource_helper', OrderResourceHelper::class)
        ->args([
            service('ekyna_commerce.provider.customer'),
            service('ekyna_resource.repository.factory'),
        ]);

    // Account order index controller
    $services
        ->set(Account\Order\IndexController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('twig'),
        ])
        ->public();

    // Account order read controller
    $services
        ->set(Account\Order\ReadController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('twig'),
        ])
        ->public();

    // Account order export controller
    $services
        ->set(Account\Order\ExportController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('ekyna_commerce.exporter.sale_csv'),
            service('ekyna_commerce.exporter.sale_xls'),
            service('ekyna_ui.helper.flash'),
            service('router'),
            param('kernel.debug'),
        ])
        ->public();

    // Account order shipment download controller
    $services
        ->set(Account\Order\Shipment\DownloadController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('ekyna_commerce.factory.document_renderer'),
            service('ekyna_ui.helper.flash'),
            service('router'),
        ])
        ->public();

    // Account order invoice download controller
    $services
        ->set(Account\Order\Invoice\DownloadController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('ekyna_commerce.factory.document_renderer'),
            service('ekyna_ui.helper.flash'),
            service('router'),
        ])
        ->public();

    // ---------------------------- Order Attachment ----------------------------

    // Account order attachment create controller
    $services
        ->set(Account\Order\Attachment\CreateController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('ekyna_commerce.helper.factory'),
            service('router'),
            service('form.factory'),
            service('ekyna_commerce.manager.order_attachment'),
            service('ekyna_ui.helper.flash'),
            service('twig'),
        ])
        ->public();

    // Account order attachment download controller
    $services
        ->set(Account\Order\Attachment\DownloadController::class)
        ->args([
            service('ekyna_commerce.account.order_resource_helper'),
            service('ekyna_commerce.filesystem'),
        ])
        ->public();

    // ---------------------------- Payment ----------------------------

    // Account payment controller
    $services
        ->set(Account\PaymentStatusController::class)
        ->args([
            service('ekyna_commerce.helper.payment'),
            service('router'),
        ])
        ->public();

    // ---------------------------- Quote ----------------------------

    // Account quote resource helper
    $services
        ->set('ekyna_commerce.account.quote_resource_helper', QuoteResourceHelper::class)
        ->args([
            service('ekyna_commerce.provider.customer'),
            service('ekyna_resource.repository.factory'),
        ]);

    // Account quote view helper
    $services
        ->set('ekyna_commerce.account.quote_view_helper', QuoteViewHelper::class)
        ->args([
            service('ekyna_commerce.manager.quote'),
            service('ekyna_commerce.helper.sale_view'),
            service('router'),
            service('twig'),
        ]);

    // Account quote index controller
    $services
        ->set(Account\Quote\IndexController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('twig'),
        ])
        ->public();

    // Account quote read controller
    $services
        ->set(Account\Quote\ReadController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.account.quote_view_helper'),
            service('twig'),
        ])
        ->public();

    // Account quote export controller
    $services
        ->set(Account\Quote\ExportController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.exporter.sale_csv'),
            service('ekyna_commerce.exporter.sale_xls'),
            service('ekyna_ui.helper.flash'),
            service('router'),
            param('kernel.debug'),
        ])
        ->public();

    // Account quote refresh controller
    $services
        ->set(Account\Quote\RefreshController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.account.quote_view_helper'),
        ])
        ->public();

    // Account quote recalculate controller
    $services
        ->set(Account\Quote\RecalculateController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.account.quote_view_helper'),
            service('ekyna_commerce.updater.sale'),
            service('ekyna_commerce.manager.quote'),
        ])
        ->public();

    // Account quote voucher controller
    $services
        ->set(Account\Quote\VoucherController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('form.factory'),
            service('ekyna_ui.helper.flash'),
            service('ekyna_commerce.helper.factory'),
            service('translator'),
            service('ekyna_commerce.manager.quote'),
            service('twig'),
        ])
        ->public();

    // ---------------------------- Quote Address ----------------------------

    // Account quote address update controller
    $services
        ->set(Account\Quote\Address\UpdateController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('form.factory'),
            service('ekyna_commerce.manager.quote'),
            service('ekyna_commerce.account.quote_view_helper'),
            service('ekyna_ui.modal.renderer'),
        ])
        ->public();

    // ---------------------------- Quote Attachment ----------------------------

    // Account quote attachment generate controller
    $services
        ->set(Account\Quote\Attachment\GenerateController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('ekyna_commerce.generator.document'),
            service('ekyna_ui.helper.flash'),
            service('ekyna_commerce.manager.quote_attachment'),
        ])
        ->public();

    // Account quote attachment create controller
    $services
        ->set(Account\Quote\Attachment\CreateController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.helper.factory'),
            service('router'),
            service('form.factory'),
            service('ekyna_commerce.manager.quote_attachment'),
            service('ekyna_ui.helper.flash'),
            service('twig'),
        ])
        ->public();

    // Account quote attachment download controller
    $services
        ->set(Account\Quote\Attachment\DownloadController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.filesystem'),
        ])
        ->public();

    // Account quote attachment refresh controller
    $services
        ->set(Account\Quote\Attachment\RefreshController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('ekyna_commerce.manager.quote_attachment'),
            service('ekyna_ui.helper.flash'),
            service('ekyna_commerce.generator.document'),
        ])
        ->public();

    // Account quote attachment delete controller
    $services
        ->set(Account\Quote\Attachment\DeleteController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('ekyna_commerce.manager.quote_attachment'),
            service('ekyna_ui.helper.flash'),
        ])
        ->public();

    // ---------------------------- Quote Item ----------------------------

    // Account quote item add controller
    $services
        ->set(Account\Quote\Item\AddController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.helper.sale'),
            service('ekyna_commerce.provider.context'),
            service('ekyna_commerce.helper.factory'),
            service('router'),
            service('ekyna_commerce.form_flow.sale_item_add'),
            service('ekyna_commerce.manager.quote'),
            service('ekyna_commerce.account.quote_view_helper'),
            service('event_dispatcher'),
            service('ekyna_ui.modal.renderer'),
        ])
        ->public();

    // Account quote item configure controller
    $services
        ->set(Account\Quote\Item\ConfigureController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.helper.sale'),
            service('router'),
            service('form.factory'),
            service('ekyna_commerce.manager.quote'),
            service('ekyna_commerce.account.quote_view_helper'),
            service('ekyna_ui.modal.renderer'),
        ])
        ->public();

    // Account quote item move controller
    $services
        ->set(Account\Quote\Item\MoveController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.helper.sale'),
            service('ekyna_commerce.manager.quote_item'),
            service('ekyna_commerce.account.quote_view_helper'),
        ])
        ->public();

    // Account quote item delete controller
    $services
        ->set(Account\Quote\Item\DeleteController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('ekyna_commerce.helper.sale'),
            service('ekyna_commerce.manager.quote_item'),
            service('ekyna_commerce.account.quote_view_helper'),
        ])
        ->public();

    // ---------------------------- Quote Payment ----------------------------

    // Account quote payment create controller
    $services
        ->set(Account\Quote\Payment\CreateController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('ekyna_commerce.helper.constants'),
            service('ekyna_ui.helper.flash'),
            service('ekyna_commerce.validator.sale_step'),
            service('ekyna_commerce.manager.payment_checkout'),
            service('ekyna_commerce.manager.quote'),
            service('ekyna_commerce.helper.payment'),
            service('twig'),
        ])
        ->public();

    // Account quote payment cancel controller
    $services
        ->set(Account\Quote\Payment\CancelController::class)
        ->args([
            service('ekyna_commerce.account.quote_resource_helper'),
            service('router'),
            service('ekyna_commerce.helper.payment'),
            service('form.factory'),
            service('twig'),
        ])
        ->public();
};
