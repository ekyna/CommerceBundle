<?php

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
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class NotificationEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotifyQueue
     */
    private $queue;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var FactoryHelperInterface
     */
    private $factory;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param NotifyQueue            $queue
     * @param Mailer                 $mailer
     * @param FactoryHelperInterface $factory
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        NotifyQueue            $queue,
        Mailer                 $mailer,
        FactoryHelperInterface $factory,
        EntityManagerInterface $manager
    ) {
        $this->queue = $queue;
        $this->mailer = $mailer;
        $this->factory = $factory;
        $this->manager = $manager;
    }

    /**
     * Kernel terminate event handler.
     */
    public function onKernelTerminate()
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
     *
     * @param Notify $notify
     */
    private function logNotification(Notify $notify)
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
            $notification->setData($source->getNumber(), 'payment');
        } elseif ($source instanceof ShipmentInterface) {
            $notification->setData($source->getNumber(), 'shipment');
        } elseif ($source instanceof InvoiceInterface) {
            $notification->setData($source->getNumber(), 'invoice');
        }

        $this->manager->persist($notification);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE  => ['onKernelTerminate', 1024], // Before Symfony EmailSenderListener
            ConsoleEvents::TERMINATE => ['onKernelTerminate', 1024], // Before Symfony EmailSenderListener
        ];
    }
}
