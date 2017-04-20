<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class InvoiceRecalculateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceRecalculateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:invoice:recalculate';

    private OrderInvoiceRepositoryInterface $invoiceRepository;
    private DocumentCalculatorInterface $invoiceCalculator;
    private EntityManagerInterface $invoiceManager;
    private string $defaultCurrency;


    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        DocumentCalculatorInterface $invoiceCalculator,
        EntityManagerInterface $invoiceManager,
        string $defaultCurrency
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->invoiceManager    = $invoiceManager;
        $this->defaultCurrency   = $defaultCurrency;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Recalculates the invoices created the given month.')
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The invoice id.')
            ->addOption('month', 'm', InputOption::VALUE_REQUIRED, 'The month date as `Y-m`.');
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
        $count    = count($invoices);
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion("Recalculate $count invoices ?", false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $confirm = function (string $number, array $diff) use ($helper, $input, $output) {
            $output->writeln('');
            $output->writeln('  Diffs:');

            foreach ($diff as $key => $amounts) {
                $output->writeln(sprintf('   * %s : %f => %f', $key, $amounts[0], $amounts[1]));
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

            if (empty($diff = $this->getDiff($oldAmounts, $newAmounts, $invoice->getCurrency()))) {
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
        ];
    }

    /**
     * Returns the invoice's amounts diff.
     */
    private function getDiff(array $a, array $b, string $currency): array
    {
        $keys = [
            'goodsBase'      => $currency,
            'discountBase'   => $currency,
            'shipmentBase'   => $currency,
            'taxesTotal'     => $currency,
            'grandTotal'     => $currency,
            'realGrandTotal' => $this->defaultCurrency,
            'paidTotal'      => $currency,
            'realPaidTotal'  => $this->defaultCurrency,
        ];

        $diff = [];

        foreach ($keys as $key => $c) {
            /** @var Decimal $aD */
            $aD = $a[$key];
            /** @var Decimal $bD */
            $bD = $b[$key];
            if (!$aD->equals($bD)) {
                $diff[$key] = [$aD->toFixed(5), $bD->toFixed(5)];
            }
        }

        return $diff;
    }
}
