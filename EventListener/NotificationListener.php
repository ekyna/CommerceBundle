<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class NotificationListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationListener
{
    public function __construct(
        private readonly NotifyQueue $queue,
        private readonly Mailer $mailer,
        private readonly FactoryHelperInterface $factory,
        private readonly EntityManagerInterface $manager
    ) {
    }

    /**
     * Kernel terminate event handler.
     */
    public function onKernelTerminate(): void
    {
        if (!$this->manager->isOpen()) {
            return;
        }

        $notifies = $this->queue->flush();

        if (empty($notifies)) {
            return;
        }

        foreach ($notifies as $notify) {
            if ($this->mailer->sendNotify($notify)) {
                $this->logNotification($notify);
            } else {
                $this->mailer->sendNotifyFailure($notify);
            }
        }

        $this->manager->flush();
    }

    /**
     * Logs the notification.
     */
    private function logNotification(Notify $notify): void
    {
        if (null === $source = $notify->getSource()) {
            return;
        }

        if ($source instanceof SaleInterface) {
            $sale = $source;
        } elseif (
            $source instanceof PaymentInterface
            || $source instanceof ShipmentInterface
            || $source instanceof InvoiceInterface
        ) {
            $sale = $source->getSale();
        } else {
            return;
        }

        $notification = $this->factory->createNotificationForSale($sale);
        $notification
            ->setSale($sale)
            ->setType($notify->getType())
            ->setDetails($notify->getReport())
            ->setSentAt(new DateTime());

        if ($source instanceof PaymentInterface) {
            $notification->setData('payment', $source->getNumber());
        } elseif ($source instanceof ShipmentInterface) {
            $notification->setData('shipment', $source->getNumber());
        } elseif ($source instanceof InvoiceInterface) {
            $notification->setData('invoice', $source->getNumber());
        }

        $this->manager->persist($notification);
    }
}
