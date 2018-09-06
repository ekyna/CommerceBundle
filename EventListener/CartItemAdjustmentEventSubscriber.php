<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CartItemAdjustmentEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class CartItemAdjustmentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemAdjustmentEventSubscriber extends BaseSubscriber
{
    /**
     * @inheritdoc
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        try {
            parent::onPreUpdate($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage($e->getMessage(), ResourceMessage::TYPE_ERROR));
        }
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage($e->getMessage(), ResourceMessage::TYPE_ERROR));
        }
    }
}
