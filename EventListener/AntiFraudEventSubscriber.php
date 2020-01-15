<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Component\Commerce\Bridge\Payum\Request\FraudLevel;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerStates;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvent;
use Ekyna\Component\Commerce\Payment\Event\PaymentEvents;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Payum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AntiFraudEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AntiFraudEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ResourceOperatorInterface
     */
    protected $cartOperator;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Payum
     */
    protected $payum;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param ResourceOperatorInterface $cartOperator
     * @param EntityManagerInterface    $entityManager
     * @param Payum                     $payum
     * @param Mailer                    $mailer
     * @param array                     $config
     */
    public function __construct(
        ResourceOperatorInterface $cartOperator,
        EntityManagerInterface $entityManager,
        Payum $payum,
        Mailer $mailer,
        array $config
    ) {
        $this->cartOperator = $cartOperator;
        $this->entityManager = $entityManager;
        $this->payum = $payum;
        $this->mailer = $mailer;

        $this->config = array_replace([
            'threshold' => 10,
        ], $config);
    }

    /**
     * Payment status event handler.
     *
     * @param PaymentEvent $event
     */
    public function onStatus(PaymentEvent $event)
    {
        // Only for failed payments
        $payment = $event->getPayment();
        if ($payment->getState() !== PaymentStates::STATE_FAILED) {
            return;
        }

        // Only for non accepted carts
        $sale = $payment->getSale();
        if (!$sale instanceof CartInterface || $sale->getState() === CartStates::STATE_ACCEPTED) {
            return;
        }

        // Check whether the sale should be considered as fraud
        if (!$this->isFraud($sale)) {
            // If not, abort
            return;
        }

        // Set customer state as fraudster
        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if (null !== $customer = $sale->getCustomer()) {
            $customer->setState(CustomerStates::STATE_FRAUDSTER);
            $this->entityManager->persist($customer);
            //$this->entityManager->flush();

            // Send email notification
            $this->mailer->sendAdminFraudsterAlert($customer);
        }

        // Delete cart
        $deleteEvent = $this->cartOperator->delete($sale);
        if ($deleteEvent->hasErrors()) {
            return;
        }

        // Set event's payment as null
        $event->setPayment(null);
        $event->stopPropagation();
    }

    /**
     * Returns whether the sale should be considered as fraud.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    protected function isFraud(SaleInterface $sale)
    {
        $level = 0;

        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface[] $failedPayments */
        foreach ($sale->getPayments(true) as $p) {
            if ($p->getState() !== PaymentStates::STATE_FAILED) {
                continue;
            }

            /** @var \Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface $method */
            $method = $p->getMethod();
            $gateway = $this->payum->getGateway($method->getGatewayName());

            try {
                $gateway->execute($request = new FraudLevel($p));
            } catch (RequestNotSupportedException $e) {
                continue;
            }

            $level += $request->getLevel();
        }

        if ($level >= $this->config['threshold']) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PaymentEvents::STATUS => ['onStatus', 1024],
        ];
    }
}
