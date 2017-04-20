<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManager;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\TicketMessageEventSubscriber as BaseSubscriber;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\User\Service\UserProviderInterface;

/**
 * Class TicketMessageEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method TicketMessageInterface getMessageFromEvent(ResourceEventInterface $event)
 */
class TicketMessageEventSubscriber extends BaseSubscriber
{
    protected UserProviderInterface $userProvider;
    protected SettingManagerInterface $settings;

    public function setUserProvider(UserProviderInterface $provider): void
    {
        $this->userProvider = $provider;
    }

    public function setSettings(SettingManager $settings): void
    {
        $this->settings = $settings;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        /** @var UserInterface $admin */
        $admin = $this->userProvider->getUser();

        if ($admin) {
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

    public function onUpdate(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $this->updateMessage($message);

        parent::onUpdate($event);
    }

    /**
     * Updates the message (do not notify without customer and user).
     */
    protected function updateMessage(TicketMessageInterface $message): void
    {
        if ($message->getTicket()->isInternal()) {
            $message
                ->setInternal(true)
                ->setNotify(false);
        } else {
            /** @var CustomerInterface $customer */
            if (!$customer = $message->getTicket()->getCustomer()) {
                $message->setNotify(false);
            } elseif (!$customer->getUser()) {
                $message->setNotify(false);
            }
        }

        $this->persistenceHelper->persistAndRecompute($message, false);
    }
}
