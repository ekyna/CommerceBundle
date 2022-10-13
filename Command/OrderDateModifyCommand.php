<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class OrderDateModifyCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderDateModifyCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:order:modify-date';
    protected static $defaultDescription = 'Modify the order dates.';

    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly EntityManagerInterface   $manager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('number', InputArgument::REQUIRED, 'The order number')
            ->addArgument('modifier', InputArgument::REQUIRED, 'The dates modifier');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Order number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new \InvalidArgumentException(
                        'Please provide a order number.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setArgument('number', $helper->ask($input, $output, $question));
        }

        if (empty($input->getArgument('modifier'))) {
            $question = new Question('Date modifier:');
            $question->setValidator(function ($answer) {
                if (!$this->validateDateModifier($answer)) {
                    throw new \InvalidArgumentException(
                        'Please provide a date modifier.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setArgument('modifier', $helper->ask($input, $output, $question));
        }
    }

    /**
     * Returns whether the date modifier is valid.
     */
    private function validateDateModifier(string $modifier): bool
    {
        if (empty($modifier)) {
            return false;
        }

        try {
            (new DateTime())->modify($modifier);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check arguments
        if (empty($number = $input->getArgument('number'))) {
            throw new \InvalidArgumentException("Empty 'number' argument.");
        }
        if (empty($modifier = $input->getArgument('modifier'))) {
            throw new \InvalidArgumentException("Empty 'modifier' argument.");
        }

        if (!$this->validateDateModifier($modifier)) {
            throw new \InvalidArgumentException(
                'Please provide a date modifier.'
            );
        }

        // Find order
        $order = $this->repository->findOneByNumber($number);
        if (null === $order) {
            throw new InvalidArgumentException("No order found for number '$number'.");
        }

        $output->writeln('<error>This is a dangerous operation.</error>');
        $output->writeln('<comment>All dates (order, invoices, payments and shipments) will be changed.</comment>');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Change order $number dates to '$modifier' ?", false
        );
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        /** @var PaymentInterface[] $payments */
        $payments = $order->getPayments()->toArray();
        /** @var ShipmentInterface[] $shipments */
        $shipments = $order->getShipments()->toArray();
        /** @var InvoiceInterface[] $invoices */
        $invoices = $order->getInvoices()->toArray();

        $co = $this->manager->getConnection();
        $co->beginTransaction();

        try {
            $this->updateDates($order, $modifier, [
                'createdAt'       => $order->getCreatedAt(),
                'updatedAt'       => $order->getUpdatedAt(),
                'completedAt'     => $order->getCompletedAt(),
                'outstandingDate' => $order->getOutstandingDate(),
            ]);

            foreach ($payments as $payment) {
                $this->updateDates($payment, $modifier, [
                    'createdAt'   => $payment->getCreatedAt(),
                    'updatedAt'   => $payment->getUpdatedAt(),
                    'completedAt' => $payment->getCompletedAt(),
                ]);
            }

            foreach ($shipments as $shipment) {
                $this->updateDates($shipment, $modifier, [
                    'createdAt'   => $shipment->getCreatedAt(),
                    'updatedAt'   => $shipment->getUpdatedAt(),
                    'completedAt' => $shipment->getCompletedAt(),
                ]);
            }

            foreach ($invoices as $invoice) {
                $this->updateDates($invoice, $modifier, [
                    'createdAt' => $invoice->getCreatedAt(),
                    'updatedAt' => $invoice->getUpdatedAt(),
                ]);
            }

            $co->commit();
        } catch (Exception $e) {
            $co->rollBack();
            throw $e;
        }

        $output->writeln('<info>Done</info>');

        return Command::SUCCESS;
    }

    /**
     * Updates the object dates by applying the given modifier.
     */
    private function updateDates(NumberSubjectInterface $object, string $modifier, array $dates): void
    {
        $couples = [];
        $parameters = [];

        foreach ($dates as $field => $date) {
            if (null !== $date) {
                $date = clone $date;
                $couples[] = "o.$field = :$field";
                $parameters[$field] = $date->modify($modifier);
            }
        }

        if (empty($parameters)) {
            return;
        }

        $query = $this->manager->createQuery(
            'UPDATE ' . get_class($object) . ' o SET ' .
            implode(', ', $couples) . ' ' .
            'WHERE o.number = :number'
        );
        foreach ($parameters as $field => $date) {
            $query->setParameter($field, $date, Types::DATETIME_MUTABLE);
        }

        $query
            ->setParameter('number', $object->getNumber())
            ->setMaxResults(1)
            ->execute();
    }
}
