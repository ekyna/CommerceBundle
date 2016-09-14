<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\ResourceEventInterface;
use Ekyna\Bundle\AdminBundle\Event\ResourceMessage;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CartEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartEventInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;

/**
 * Class CartEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartEventSubscriber extends BaseSubscriber
{
    /**
     * Pre delete event handler.
     *
     * @param CartEventInterface $event
     */
    public function onPreDelete(CartEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            /** @var ResourceEventInterface $event */
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.cart.message.cant_be_deleted', // TODO
                ResourceMessage::TYPE_ERROR
            ));
        }
    }

    /**
     * @inheritdoc
     */
    protected function handleIdentity(CartInterface $cart)
    {
        $changed = false;

        /**
         * @var \Ekyna\Bundle\CommerceBundle\Model\CartInterface $cart
         * @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer
         */
        if (null !== $customer = $cart->getCustomer()) {
            if (0 == strlen($cart->getGender())) {
                $cart->setGender($customer->getGender());
                $changed = true;
            }
        }

        return $changed || parent::handleIdentity($cart);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array_merge(parent::getSubscribedEvents(), [
            CartEvents::PRE_DELETE => ['onPreDelete', 0],
        ]);
    }
}
