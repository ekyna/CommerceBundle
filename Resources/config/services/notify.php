<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\NotificationEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\NotifyEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Service\Notify\RecipientHelper;
use Ekyna\Component\Commerce\Common\Listener\AbstractNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\InvoiceNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\OrderNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\PaymentNotifyListener;
use Ekyna\Component\Commerce\Common\Listener\ShipmentNotifyListener;
use Ekyna\Component\Commerce\Common\Notify\NotifyBuilder;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Notify builder
        ->set('ekyna_commerce.builder.notify', NotifyBuilder::class)
            ->args([
                service('event_dispatcher'),
            ])

        // Notify queue
        ->set('ekyna_commerce.queue.notify', NotifyQueue::class)
            ->args([
                service('ekyna_commerce.builder.notify'),
            ])

        // Notify helper
        ->set('ekyna_commerce.helper.notify', RecipientHelper::class)
            ->args([
                service('ekyna_setting.manager'),
                service('ekyna_admin.provider.user'),
                service('ekyna_admin.repository.user'),
                param('ekyna_commerce.default.notify')
            ])

        // Notify event listener
        ->set('ekyna_commerce.listener.notify', NotifyEventSubscriber::class)
            ->args([
                service('ekyna_commerce.repository.notify_model'),
                service('ekyna_commerce.helper.notify'),
                service('ekyna_commerce.helper.shipment'),
                service('ekyna_user.manager.token'),
                service('router'),
                service('translator'),
            ])
            ->tag('kernel.event_subscriber')

        // Notification event listener
        ->set('ekyna_commerce.listener.notification', NotificationEventSubscriber::class)
            ->args([
                service('ekyna_commerce.queue.notify'),
                service('ekyna_commerce.mailer'),
                service('ekyna_commerce.factory.sale'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('kernel.event_subscriber')

        // Abstract notify listener
        ->set('ekyna_commerce.listener.notify.abstract', AbstractNotifyListener::class)
            ->abstract(true)
            ->args([
                service('ekyna_resource.orm.persistence_tracker'),
                service('ekyna_commerce.queue.notify'),
                service('ekyna_commerce.builder.notify'),
            ])

        // Order notify listener
        ->set('ekyna_commerce.listener.notify.order', OrderNotifyListener::class)
            ->parent('ekyna_commerce.listener.notify.abstract')
            ->tag('doctrine.orm.entity_listener')

        // Order payment notify listener
        ->set('ekyna_commerce.listener.notify.order_payment', PaymentNotifyListener::class)
            ->parent('ekyna_commerce.listener.notify.abstract')
            ->tag('doctrine.orm.entity_listener')

        // Order shipment notify listener
        ->set('ekyna_commerce.listener.notify.order_shipment', ShipmentNotifyListener::class)
            ->parent('ekyna_commerce.listener.notify.abstract')
            ->tag('doctrine.orm.entity_listener')

        // Order invoice notify listener
        ->set('ekyna_commerce.listener.notify.order_invoice', InvoiceNotifyListener::class)
            ->parent('ekyna_commerce.listener.notify.abstract')
            ->tag('doctrine.orm.entity_listener')
    ;
};
