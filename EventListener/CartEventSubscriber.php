<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CartEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class CartEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartEventSubscriber extends BaseSubscriber
{
    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.cart.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            ));
        }
    }
}
