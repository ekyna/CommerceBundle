<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\NotificationListener;
use Ekyna\Bundle\CommerceBundle\EventListener\NotifyEventListener;
use Ekyna\Bundle\CommerceBundle\Service\Notify\RecipientHelper;
use Ekyna\Component\Commerce\Common\Event\NotifyEvents;
use Ekyna\Component\Commerce\Common\Listener\AbstractNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\InvoiceNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\OrderNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\PaymentNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\ShipmentNotifyListener;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\HttpKernel\KernelEvents;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Notify builder
    $services
        ->set('ekyna_commerce.builder.notify', NotifyBuilder::class)
        ->args([
            service('event_dispatcher'),
        ]);

    // Notify queue
    $services
        ->set('ekyna_commerce.queue.notify', NotifyQueue::class)
        ->args([
            service('ekyna_commerce.builder.notify'),
        ]);

    // Notify helper
    $services
        ->set('ekyna_commerce.helper.notify', RecipientHelper::class)
        ->args([
            service('ekyna_setting.manager'),
            service('ekyna_admin.provider.user'),
            service('ekyna_admin.repository.user'),
            param('ekyna_commerce.default.notify'),
        ]);

    // Notify event listener
    $services
        ->set('ekyna_commerce.listener.notify', NotifyEventListener::class)
        ->args([
            service('ekyna_commerce.repository.notify_model'),
            service('ekyna_commerce.helper.notify'),
            service('ekyna_commerce.helper.shipment'),
            service('router'),
            service('translator'),
            service('ekyna_user.security.login_link_helper'),
        ])
        ->tag('kernel.event_listener', [
            'event'    => NotifyEvents::BUILD,
            'method'   => 'buildSubject',
            'priority' => -1,
        ])
        ->tag('kernel.event_listener', [
            'event'    => NotifyEvents::BUILD,
            'method'   => 'buildRecipients',
            'priority' => -2,
        ])
        ->tag('kernel.event_listener', [
            'event'    => NotifyEvents::BUILD,
            'method'   => 'buildContent',
            'priority' => -3,
        ])
        ->tag('kernel.event_listener', [
            'event'    => NotifyEvents::BUILD,
            'method'   => 'buildButton',
            'priority' => -4,
        ])
        ->tag('kernel.event_listener', [
            'event'    => NotifyEvents::BUILD,
            'method'   => 'finalize',
            'priority' => -2048,

        ]);

    // Notification listener
    $services
        ->set('ekyna_commerce.listener.notification', NotificationListener::class)
        ->args([
            service('ekyna_commerce.queue.notify'),
            service('ekyna_commerce.mailer'),
            service('ekyna_commerce.helper.factory'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('kernel.event_listener', [
            'event'    => KernelEvents::TERMINATE,
            'method'   => 'onKernelTerminate',
            'priority' => 1024, // Before Symfony EmailSenderListener
        ])
        ->tag('kernel.event_listener', [
            'event'    => ConsoleEvents::TERMINATE,
            'method'   => 'onKernelTerminate',
            'priority' => 1024, // Before Symfony EmailSenderListener
        ]);

    // Abstract notify listener
    $services
        ->set('ekyna_commerce.listener.notify.abstract', AbstractNotifyListener::class)
        ->abstract(true)
        ->args([
            service('ekyna_resource.orm.persistence_tracker'),
            service('ekyna_commerce.queue.notify'),
            service('ekyna_commerce.builder.notify'),
        ]);

    // Order notify listener
    $services
        ->set('ekyna_commerce.listener.notify.order', OrderNotifyListener::class)
        ->parent('ekyna_commerce.listener.notify.abstract')
        ->tag('doctrine.orm.entity_listener');

    // Order payment notify listener
    $services
        ->set('ekyna_commerce.listener.notify.order_payment', PaymentNotifyListener::class)
        ->parent('ekyna_commerce.listener.notify.abstract')
        ->tag('doctrine.orm.entity_listener');

    // Order shipment notify listener
    $services
        ->set('ekyna_commerce.listener.notify.order_shipment', ShipmentNotifyListener::class)
        ->parent('ekyna_commerce.listener.notify.abstract')
        ->tag('doctrine.orm.entity_listener');

    // Order invoice notify listener
    $services
        ->set('ekyna_commerce.listener.notify.order_invoice', InvoiceNotifyListener::class)
        ->parent('ekyna_commerce.listener.notify.abstract')
        ->tag('doctrine.orm.entity_listener');
};
