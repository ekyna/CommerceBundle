<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Service\Security\UserProviderInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManager;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\TicketMessageEventSubscriber as BaseSubscriber;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class TicketMessageEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method TicketMessageInterface getMessageFromEvent(ResourceEventInterface $event)
 */
class TicketMessageEventSubscriber extends BaseSubscriber
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var SettingsManager
     */
    protected $settings;


    /**
     * Sets the user provider.
     *
     * @param UserProviderInterface $provider
     */
    public function setUserProvider(UserProviderInterface $provider): void
    {
        $this->userProvider = $provider;
    }

    /**
     * Sets the settings.
     *
     * @param SettingsManager $settings
     */
    public function setSettings(SettingsManager $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        if (null !== $admin = $this->userProvider->getUser()) {
            $message->setAdmin($admin);

            if ($admin->hasShortName()) {
                $message->setAuthor($admin->getShortName());
            } else {
                $message->setAuthor($this->settings->getParameter('general.site_name'));
            }
        } else {
            $customer = $message->getTicket()->getCustomer();
            $message->setAuthor($customer->getFirstName() . ' ' . $customer->getLastName());
        }

        $this->updateMessage($message);

        parent::onInsert($event);
    }

    /**
     * @inheritDoc
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $this->updateMessage($message);

        parent::onUpdate($event);
    }

    /**
     * Updates the message (do not notify without customer and user).
     *
     * @param TicketMessageInterface $message
     */
    protected function updateMessage(TicketMessageInterface $message): void
    {
        if ($message->getTicket()->isInternal()) {
            $message
                ->setInternal(true)
                ->setNotify(false);
        } else {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
            if (!$customer = $message->getTicket()->getCustomer()) {
                $message->setNotify(false);
            } elseif (!$customer->getUser()) {
                $message->setNotify(false);
            }
        }

        $this->persistenceHelper->persistAndRecompute($message, false);
    }
}
