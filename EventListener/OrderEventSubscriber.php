<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Event\ResourceMessage;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber extends BaseSubscriber
{
    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            /** @var \Ekyna\Bundle\AdminBundle\Event\ResourceEventInterface $event */
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.order.message.cant_be_deleted', // TODO
                ResourceMessage::TYPE_ERROR
            ));
        }
    }

    /**
     * @inheritdoc
     */
    protected function handleIdentity(SaleInterface $sale)
    {
        if (!$sale instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface.");
        }

        $changed = false;

        /**
         * @var \Ekyna\Bundle\CommerceBundle\Model\OrderInterface $sale
         * @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer
         */
        if (null !== $customer = $sale->getCustomer()) {
            if (0 == strlen($sale->getGender())) {
                $sale->setGender($customer->getGender());
                $changed = true;
            }
        }

        return $changed || parent::handleIdentity($sale);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array_merge(parent::getSubscribedEvents(), [
            OrderEvents::PRE_DELETE => ['onPreDelete', 0],
        ]);
    }
}
