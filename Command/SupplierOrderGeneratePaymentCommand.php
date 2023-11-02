<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdaterInterface;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

/**
 * Class SupplierOrderGeneratePaymentCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderGeneratePaymentCommand extends Command
{
    public static $defaultName        = 'ekyna:commerce:supplier_order:generate_payments';
    public static $defaultDescription = 'Migration helper to generate payments for the supplier orders.';

    private string     $description;
    private ?Statement $createPaymentQuery = null;
    private ?Statement $updateOrderQuery   = null;
    private ?Query     $selectOrderQuery   = null;

    public function __construct(
        private readonly SupplierOrderUpdaterInterface $updater,
        private readonly CurrencyConverterInterface    $converter,
        private readonly ResourceRegistryInterface     $registry,
        private readonly EntityManagerInterface        $manager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::OPTIONAL, 'The ID of the supplier order to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->description = sprintf('(generated at %s)', (new DateTime())->format('Y-m-d'));

        if (null !== $id = $input->getArgument('id')) {
            $order = $this
                ->getSelectOrderQuery()
                ->setParameter('id', $id)->getOneOrNullResult();

            if (!$order instanceof SupplierOrderInterface) {
                $output->writeln("Order with id $id not found.");

                return Command::FAILURE;
            }

            $this->updateOrder($order, $output);

            return Command::SUCCESS;
        }

        $io = new SymfonyStyle($input, $output);
        if (!$io->confirm('Do you want to continue ?')) {
            return Command::SUCCESS;
        }

        $results = $this->selectOrders();

        foreach ($results as $result) {
            $order = $this
                ->getSelectOrderQuery()
                ->setParameter('id', $result['id'])->getOneOrNullResult();

            if (!$order instanceof SupplierOrderInterface) {
                continue;
            }

            $this->updateOrder($order, $output);
        }

        return Command::SUCCESS;
    }

    private function updateOrder(SupplierOrderInterface $order, OutputInterface $output): void
    {
        $output->write(
            sprintf(
                '<comment>[%d] %s</comment> ... ',
                $order->getId(),
                $order->getNumber()
            )
        );

        if (null === $order->getSupplier()?->getAddress()?->getCountry()) {
            $output->writeln('<error>country</error>');

            return;
        }

        $this->generateSupplierPayment($order);
        $this->generateForwarderPayment($order);

        $this->updateOrderTotals($order);

        $output->writeln('<info>done</info>');
    }

    private function updateOrderTotals(SupplierOrderInterface $order): void
    {
        if (!$this->updater->updatePaidTotals($order)) {
            return;
        }

        $this->updater->updateState($order);

        $this->getUpdateOrderQuery()->execute([
            'payment'   => $order->getPaymentPaidTotal()->toFixed(5),
            'forwarder' => $order->getForwarderPaidTotal()->toFixed(5),
            'state'     => $order->getState(),
            'id'        => $order->getId(),
        ]);
    }

    private function selectOrders(): array
    {
        $orderClass = $this->registry->find('ekyna_commerce.supplier_order')->getEntityClass();
        $paymentClass = $this->registry->find('ekyna_commerce.supplier_payment')->getEntityClass();

        return $this
            ->manager
            ->createQuery(
                <<<DQL
                SELECT s.id 
                FROM $orderClass s 
                WHERE (
                       (
                           s.paymentTotal > 0
                           AND s.paymentPaidTotal != s.paymentTotal 
                           AND s.paymentDate IS NOT NULL
                       )
                       OR (
                           s.forwarderTotal > 0
                           AND s.forwarderPaidTotal != s.forwarderTotal 
                           AND s.forwarderDate IS NOT NULL
                       )
                   )
                   AND NOT EXISTS (
                       SELECT 1 FROM $paymentClass p WHERE p.order = s
                   )
            DQL
            )
            ->getScalarResult();
    }

    private function generateSupplierPayment(SupplierOrderInterface $order): void
    {
        $amount = $order->getPaymentTotal()->sub($order->getPaymentPaidTotal());
        if (0 >= $amount) {
            return;
        }

        if (null === $date = $order->getPaymentDate()) {
            return;
        }

        $date = $order->getExchangeDate() ?? $date;

        $this->createPayment($order, $amount, $date, false);
    }

    private function generateForwarderPayment(SupplierOrderInterface $order): void
    {
        $amount = $order->getForwarderTotal()->sub($order->getForwarderPaidTotal());
        if (0 >= $amount) {
            return;
        }

        if (null === $date = $order->getForwarderDate()) {
            return;
        }

        $this->createPayment($order, $amount, $date, true);
    }

    private function createPayment(
        SupplierOrderInterface $order,
        Decimal                $amount,
        DateTimeInterface      $date,
        bool                   $toForwarder
    ): void {
        $rate = $order->getExchangeRate() ?? $this->converter->getRate(
            $this->converter->getDefaultCurrency(),
            $order->getCurrency()->getCode(),
            $date
        );

        $this
            ->getCreatePaymentQuery()
            ->execute([
                'amount'            => $amount->toFixed(5),
                'description'       => $this->description,
                'created_at'        => $date->format('Y-m-d H:i:s'),
                'state'             => PaymentStates::STATE_CAPTURED,
                'supplier_order_id' => $order->getId(),
                'to_forwarder'      => $toForwarder ? 1 : 0,
                'currency_id'       => $order->getCurrency()->getId(),
                'exchange_rate'     => $rate->toFixed(5),
                'exchange_date'     => $date->format('Y-m-d H:i:s'),
            ]);
    }

    private function getSelectOrderQuery(): Query
    {
        if ($this->selectOrderQuery) {
            return $this->selectOrderQuery;
        }

        $orderClass = $this->registry->find('ekyna_commerce.supplier_order')->getEntityClass();

        return $this->selectOrderQuery = $this->manager->createQuery(
        /** @lang dql */ "SELECT s FROM $orderClass s WHERE s.id = :id"
        );
    }

    private function getCreatePaymentQuery(): Statement
    {
        if ($this->createPaymentQuery) {
            return $this->createPaymentQuery;
        }

        $connection = $this->manager->getConnection();

        return $this->createPaymentQuery = $connection
            ->prepare(
            /** @lang SQL */ <<<DQL
                INSERT INTO commerce_supplier_payment(amount, description, created_at, state, supplier_order_id, to_forwarder, currency_id, exchange_date, exchange_rate)
                VALUES (:amount, :description, :created_at, :state, :supplier_order_id, :to_forwarder, :currency_id, :exchange_date, :exchange_rate)
                DQL
            );
    }

    private function getUpdateOrderQuery(): Statement
    {
        if ($this->updateOrderQuery) {
            return $this->updateOrderQuery;
        }

        $connection = $this->manager->getConnection();

        return $this->updateOrderQuery = $connection
            ->prepare(
            /** @lang SQL */ <<<DQL
                UPDATE commerce_supplier_order o 
                SET o.payment_paid_total = :payment, 
                    o.forwarder_paid_total = :forwarder, 
                    o.state = :state
                WHERE o.id = :id
                DQL
            );
    }
}
