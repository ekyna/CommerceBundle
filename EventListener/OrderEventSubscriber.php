<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\ResourceEventInterface;
use Ekyna\Bundle\AdminBundle\Event\ResourceMessage;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderListener;
use Ekyna\Component\Commerce\Order\Model\OrderEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber extends OrderListener implements EventSubscriberInterface
{
    /**
     * Pre delete event handler.
     *
     * @param OrderEventInterface $event
     */
    public function onPreDelete(OrderEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            /** @var ResourceEventInterface $event */
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.order.message.cant_be_deleted', // TODO
                ResourceMessage::TYPE_ERROR
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            /*OrderEvents::CONTENT_CHANGE => [
                ['onPreContentChange', 512],
                ['onContentChange', 0],
                ['onPostContentChange', -512],
            ],
            OrderEvents::STATE_CHANGE   => [
                ['onStateChange', 0],
                ['onPostStateChange', -512],
            ],*/

            OrderEvents::INSERT     => ['onInsert', 0],
            OrderEvents::UPDATE     => ['onUpdate', 0],

            OrderEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
