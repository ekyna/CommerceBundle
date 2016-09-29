<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderShipmentEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderShipmentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentEventSubscriber extends BaseSubscriber
{
    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.shipment.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            ));
        }
    }
}
