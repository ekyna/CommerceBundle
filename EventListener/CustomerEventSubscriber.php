<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CustomerEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CustomerEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerEventSubscriber extends BaseSubscriber
{
    /**
     * @var InChargeResolver
     */
    protected $inChargeResolver;


    /**
     * Sets the 'in charge' resolver.
     *
     * @param InChargeResolver $inChargeResolver
     */
    public function setInChargeResolver(InChargeResolver $inChargeResolver)
    {
        $this->inChargeResolver = $inChargeResolver;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        $customer = $this->getCustomerFromEvent($event);

        $this->inChargeResolver->update($customer);
    }

    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        parent::onUpdate($event);

        $customer = $this->getCustomerFromEvent($event);

        if ($this->persistenceHelper->isChanged($customer, ['inCharge'])) {
            $this->scheduleParentChangeEvents($customer);
        }
    }

    /**
     * @inheritDoc
     */
    protected function updateFromParent(CustomerInterface $customer)
    {
        $changed = parent::updateFromParent($customer);

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if ($customer->hasParent() && null === $customer->getInCharge()) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $parent */
            $parent = $customer->getParent();
            if (null !== $inCharge = $parent->getInCharge()) {
                $customer->setInCharge($inCharge);

                $changed = true;
            }
        }

        return $changed;
    }


    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array_merge(parent::getSubscribedEvents(), [
            CustomerEvents::INITIALIZE => ['onInitialize', 0],
        ]);
    }
}
