<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function count;

/**
 * Class InvoiceUpdateTotalsCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceUpdateTotalsCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:invoice:update:totals';
    protected static $defaultDescription = 'Updates the invoices totals.';

    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoiceCalculatorInterface     $invoiceCalculator,
        private readonly EntityManagerInterface          $invoiceManager,
        private readonly LockChecker                     $lockChecker
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The invoice id.')
            ->addOption('month', 'm', InputOption::VALUE_REQUIRED, 'The month date as `Y-m`.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Whether to force update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $invoices = [];
        if (null !== $id = $input->getOption('id')) {
            if (null === $invoice = $this->invoiceRepository->find((int)$id)) {
                $output->writeln("<error>Invoice #$id not found</error>");

                return Command::FAILURE;
            }

            $invoices = [$invoice];
        } elseif (null !== $month = $input->getOption('month')) {
            if (false === $date = DateTime::createFromFormat('Y-m-d', $month . '-01')) {
                $output->writeln("<error>Failed to parse '$month'</error>");

                return Command::FAILURE;
            }

            $invoices = $this->invoiceRepository->findByMonth($date);
        }

        // Confirmations
        $output->writeln('<error>This is a dangerous operation.</error>');
        $count = count($invoices);
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Recalculate $count invoices ?", false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        if ($input->getOption('force')) {
            $this->lockChecker->setEnabled(false);
        }

        $confirm = function (string $number, array $diff) use ($helper, $input, $output) {
            $output->writeln('');
            $output->writeln('  Diffs:');

            foreach ($diff as $key => $amounts) {
                $output->writeln(sprintf('   * %s : %s => %s', $key, $amounts[0], $amounts[1]));
            }

            $question = new ConfirmationQuestion("  Confirm invoice $number update ?", false);

            return $helper->ask($input, $output, $question);
        };

        // Recalculation
        $count = 0;
        foreach ($invoices as $invoice) {
            $output->write(sprintf(
                '<comment>[%s - %d] %s</comment> ... ',
                $invoice->getSale()->getId(),
                $invoice->getId(),
                $invoice->getNumber()
            ));

            $oldAmounts = $this->getAmounts($invoice);
            $this->invoiceCalculator->calculate($invoice);
            $newAmounts = $this->getAmounts($invoice);

            if (empty($diff = $this->getDiff($oldAmounts, $newAmounts))) {
                $output->writeln('<comment>up to date</comment>');

                continue;
            } elseif (!$confirm($invoice->getNumber(), $diff)) {
                $output->writeln(' ... <error>abort</error>');

                $this->invoiceManager->clear();

                continue;
            }

            $this->invoiceManager->persist($invoice);
            $count++;

            $output->writeln('<info>done</info>');

            if ($count % 10 === 0) {
                $this->invoiceManager->flush();
            }
        }

        if ($count % 10 !== 0) {
            $this->invoiceManager->flush();
        }

        return Command::SUCCESS;
    }

    /**
     * Extracts the invoice amounts.
     */
    private function getAmounts(InvoiceInterface $invoice): array
    {
        return [
            'goodsBase'      => $invoice->getGoodsBase(),
            'discountBase'   => $invoice->getDiscountBase(),
            'shipmentBase'   => $invoice->getShipmentBase(),
            'taxesTotal'     => $invoice->getTaxesTotal(),
            'grandTotal'     => $invoice->getGrandTotal(),
            'realGrandTotal' => $invoice->getRealPaidTotal(),
            'paidTotal'      => $invoice->getPaidTotal(),
            'realPaidTotal'  => $invoice->getRealPaidTotal(),
            'taxesDetails'   => $invoice->getTaxesDetails(),
        ];
    }

    /**
     * Returns the invoice's amounts diff.
     */
    private function getDiff(array $a, array $b): array
    {
        $decimalDiff = function (Decimal $a, Decimal $b): ?array {
            if ($a->equals($b)) {
                return null;
            }

            return [$a->toFixed(5), $b->toFixed(5)];
        };

        $arrayDiff = function (array $a, array $b): ?array {
            if ($a === $b) {
                return null;
            }

            return ['array(' . count($a) . ')', 'array(' . count($b) . ')'];
        };

        $keys = [
            'goodsBase'      => $decimalDiff,
            'discountBase'   => $decimalDiff,
            'shipmentBase'   => $decimalDiff,
            'taxesTotal'     => $decimalDiff,
            'grandTotal'     => $decimalDiff,
            'realGrandTotal' => $decimalDiff,
            'paidTotal'      => $decimalDiff,
            'realPaidTotal'  => $decimalDiff,
            'taxesDetails'   => $arrayDiff,
        ];

        $diff = [];

        foreach ($keys as $key => $closure) {
            if (null !== $d = $closure($a[$key], $b[$key])) {
                $diff[$key] = $d;
            }
        }

        return $diff;
    }
}
