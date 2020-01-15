<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Migration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdaterInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Repository\PaymentMethodRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreditToRefundMigrator
 * @package Ekyna\Bundle\CommerceBundle\Service\Migration
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditToRefundMigrator
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $methodRepository;

    /**
     * @var PaymentFactoryInterface
     */
    private $paymentFactory;

    /**
     * @var PaymentUpdaterInterface
     */
    private $paymentUpdater;

    /**
     * @var CustomerUpdaterInterface
     */
    private $customerUpdater;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var NotifyQueue
     */
    private $notifyQueue;

    /**
     * @var string
     */
    private $orderClass;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Query
     */
    private $nextOrderQuery;

    /**
     * @var int
     */
    private $lastId;

    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $clearCustomerCredit;

    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $clearCustomerOutstanding;

    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $getMethodId;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface           $manager
     * @param PaymentMethodRepositoryInterface $methodRepository
     * @param PaymentFactoryInterface          $paymentFactory
     * @param PaymentUpdaterInterface          $paymentUpdater
     * @param CustomerUpdaterInterface         $customerUpdater
     * @param CurrencyConverterInterface       $currencyConverter
     * @param NotifyQueue                      $notifyQueue
     * @param string                           $orderClass
     */
    public function __construct(
        EntityManagerInterface $manager,
        PaymentMethodRepositoryInterface $methodRepository,
        PaymentFactoryInterface $paymentFactory,
        PaymentUpdaterInterface $paymentUpdater,
        CustomerUpdaterInterface $customerUpdater,
        CurrencyConverterInterface $currencyConverter,
        NotifyQueue $notifyQueue,
        string $orderClass
    ) {
        $this->manager           = $manager;
        $this->methodRepository  = $methodRepository;
        $this->paymentFactory    = $paymentFactory;
        $this->paymentUpdater    = $paymentUpdater;
        $this->customerUpdater   = $customerUpdater;
        $this->currencyConverter = $currencyConverter;
        $this->notifyQueue       = $notifyQueue;
        $this->orderClass        = $orderClass;
    }

    /**
     * Sets the output.
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output = null): void
    {
        $this->output = $output;
    }

    /**
     * Migrates credits to refunds.
     *
     * @param int  $fromId
     * @param bool $single
     */
    public function migrate(int $fromId = 0, bool $single = false): void
    {
        $this->lastId = $fromId;

        $connection = $this->manager->getConnection();

        $this->clearCustomerCredit = $connection
            ->prepare('UPDATE commerce_customer SET credit_balance=0 WHERE id=:id LIMIT 1');

        $this->clearCustomerOutstanding = $connection
            ->prepare('UPDATE commerce_customer SET outstanding_balance=0 WHERE id=:id LIMIT 1');

        $this->getMethodId = $connection
            ->prepare('SELECT i.payment_method_id FROM commerce_order_invoice i WHERE i.id=:id LIMIT 1');

        try {
            while ($order = $this->getNextOrder()) {
                $this->migrateOrder($order);

                if ($single) {
                    break;
                }
            }
        } catch (\Exception $e) {
            $stop = true; // TODO (?)
        } catch (\Throwable $e) {
            $stop = true; // TODO (?)
        }

        // Prevent automatic notifications
        $this->notifyQueue->clear();
    }

    private function migrateOrder(OrderInterface $order): void
    {
        if ($this->output) {
            $this->output->writeln("Order " . $order->getNumber() . " (" . $order->getId() . ')');
        }

        $this->lastId = $order->getId();

        $credit = $outstanding = null;
        if ($customer = $order->getCustomer()) {
            $credit      = $customer->getCreditBalance();
            $outstanding = $customer->getOutstandingBalance();
        }

        $methods = [];
        foreach ($order->getInvoices(false) as $invoice) {
            $this->getMethodId->execute(['id' => $invoice->getId()]);

            /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface $method */
            $method = null;
            if (0 < $methodId = (int)$this->getMethodId->fetchColumn(0)) {
                $method = $this->methodRepository->find($methodId);
            }

            $name = $method ? $method->getName() : 'None';

            if (is_null($method) || !($method->isManual() || $method->isCredit())) {
                $method = $this->methodRepository->findOneByFactoryName(Offline::FACTORY_NAME);
            }

            $id = $method->getId();
            if (!isset($methods[$id])) {
                $methods[$id] = [
                    'method' => $method,
                    'amount' => 0,
                    'date'   => null,
                ];
            }

            $methods[$id]['amount'] += $amount = $invoice->getGrandTotal();
            $methods[$id]['date']   = $invoice->getCreatedAt();

            if ($this->output) {
                $this->output->writeln(sprintf('[I] %s : %s', $name, round($amount, 2)));
            }
        }

        if (0 < $paid = $order->getPaidTotal()) {
            krsort($methods);

            foreach ($methods as $data) {
                $amount = min($paid, $data['amount']);
                if (0 >= $amount) {
                    break;
                }

                $method = $data['method'];

                $payment = $this->paymentFactory->createRefund($order, $method);

                $payment->setExchangeDate($data['date']);

                $rate = $this
                    ->currencyConverter
                    ->getSubjectExchangeRate(
                        $payment,
                        $this->currencyConverter->getDefaultCurrency(),
                        $payment->getCurrency()->getCode()
                    );

                $payment
                    ->setExchangeRate($rate)
                    ->setState(PaymentStates::STATE_CAPTURED)
                    ->setCreatedAt($data['date'])
                    ->setCompletedAt($data['date'])
                    ->setDescription(sprintf("%s : généré d'après l'avoir %s\n", date('d/m/Y'), $invoice->getNumber()));

                $this->paymentUpdater->updateAmount($payment, $amount);

                $order->addPayment($payment);

                $this->manager->persist($payment);

                if ($customer && $method->isCredit()) {
                    $this->customerUpdater->updateCreditBalance($customer, -$data['amount'], true);
                }

                if ($this->output) {
                    $this->output->writeln(sprintf('[P] %s : %s', $method->getName(), round($amount, 2)));
                }
            }
        }

        $this->manager->flush();
        $this->manager->clear();

        if ($customer) {
            if (0 !== Money::compare($credit, $value = $customer->getCreditBalance(),
                    $this->currencyConverter->getDefaultCurrency())) {
                if (0.05 > abs($credit)) {
                    $this->clearCustomerCredit->execute(['id' => $customer->getId()]);
                    $this->output->writeln('<comment>[C] Credit cleared</comment>');
                } else {
                    $this->output->writeln(sprintf('<error>[C] Credit %s : %s</error>', $credit, $value));
                }
            }

            if (0 !== Money::compare($outstanding, $value = $customer->getOutstandingBalance(),
                    $this->currencyConverter->getDefaultCurrency())) {
                if (0.05 > abs($outstanding)) {
                    $this->clearCustomerOutstanding->execute(['id' => $customer->getId()]);
                    $this->output->writeln('<comment>[C] Outstanding cleared</comment>');
                } else {
                    $this->output->writeln(sprintf('<error>[C] Outstanding %s : %s</error>', $outstanding, $value));
                }
            }
        }
    }

    /**
     * Returns the next order.
     *
     * @return OrderInterface|null
     */
    private function getNextOrder(): ?OrderInterface
    {
        return $this
            ->getNextOrderQuery()
            ->setParameters([
                'type' => DocumentTypes::TYPE_CREDIT,
                'id'   => $this->lastId,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Returns the next order query.
     *
     * @return Query
     */
    private function getNextOrderQuery(): Query
    {
        if ($this->nextOrderQuery) {
            return $this->nextOrderQuery;
        }

        $qb = $this
            ->manager
            ->createQueryBuilder()
            ->from($this->orderClass, 'o')
            ->select('o');

        $ex = $qb->expr();

        return $this->nextOrderQuery = $qb
            ->join('o.invoices', 'i')
            ->andWhere($ex->eq('i.type', ':type'))
            ->andWhere($ex->gt('o.id', ':id'))
            ->getQuery()
            ->setMaxResults(1);
    }
}
