<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CustomerEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

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
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var CurrencyProviderInterface
     */
    protected $currencyProvider;


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
     * Sets the locale provider.
     *
     * @param LocaleProviderInterface $provider
     */
    public function setLocaleProvider(LocaleProviderInterface $provider)
    {
        $this->localeProvider = $provider;
    }

    /**
     * Sets the currency provider.
     *
     * @param CurrencyProviderInterface $provider
     */
    public function setCurrencyProvider(CurrencyProviderInterface $provider)
    {
        $this->currencyProvider = $provider;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event): void
    {
        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        $customer = $this->getCustomerFromEvent($event);

        $this->inChargeResolver->update($customer);

        if (null === $customer->getLocale()) {
            $customer->setLocale($this->localeProvider->getCurrentLocale());
        }
        if (null === $customer->getCurrency()) {
            $customer->setCurrency($this->currencyProvider->getCurrency());
        }
    }

    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceEventInterface $event): void
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
    protected function updateFromParent(CustomerInterface $customer): bool
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
