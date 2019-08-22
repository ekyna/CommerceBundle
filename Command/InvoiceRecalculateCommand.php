<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
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
    /**
     * @var OrderInvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var DocumentCalculatorInterface
     */
    private $invoiceCalculator;

    /**
     * @var EntityManagerInterface
     */
    private $invoiceManager;


    /**
     * Constructor.
     *
     * @param OrderInvoiceRepositoryInterface $invoiceRepository
     * @param DocumentCalculatorInterface     $invoiceCalculator
     * @param EntityManagerInterface          $invoiceManager
     */
    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        DocumentCalculatorInterface $invoiceCalculator,
        EntityManagerInterface $invoiceManager
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->invoiceManager = $invoiceManager;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:invoice:recalculate')
            ->setDescription('Recalculates the invoices created the given month.')
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The invoice id.')
            ->addOption('month', 'm', InputOption::VALUE_REQUIRED, 'The month date as `Y-m`.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invoices = [];
        if (null !== $id = $input->getOption('id')) {
            if (null === $invoice = $this->invoiceRepository->find($id)) {
                $output->writeln("<error>Invoice #$id not found</error>");

                return;
            }

            $invoices = [$invoice];
        } elseif (null !== $month = $this->createDate($input->getArgument('month'))) {
            if (false === $date = \DateTime::createFromFormat('Y-m-d', $month . '-01')) {
                $output->writeln("<error>Failed to parse '$month'</error>");

                return;
            }

            $invoices = $this->invoiceRepository->findByMonth($date);
        }

        // Confirmation
        $output->writeln('<error>This is a dangerous operation.</error>');
        $count = count($invoices);
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Recalculate $count invoices ?", false
        );
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $confirm = function($number, $old, $new) use ($helper, $input, $output) {
            $question = new ConfirmationQuestion(
                "Update invoice $number total from $old to $new ?", false
            );
            return $helper->ask($input, $output, $question);
        };

        // Recalculation
        $count = 0;
        foreach ($invoices as $invoice) {
            $output->write(sprintf(
                '<comment>[%d] %s</comment> ... ',
                $invoice->getId(),
                $invoice->getNumber()
            ));

            $oldTotal = $invoice->getGrandTotal();

            $this->invoiceCalculator->calculate($invoice);

            $newTotal = $invoice->getGrandTotal();

            $currency = $invoice->getCurrency();

            if (0 !== Money::compare($oldTotal, $newTotal, $currency)) {
                if (!$confirm($invoice->getNumber(), $oldTotal, $newTotal)) {
                    $output->writeln('<comment>abort</comment>');

                    return;
                }
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
    }

    /**
     * Creates the date from the given string.
     *
     * @param string $date
     *
     * @return bool
     */
    private function createDate(string $date = null)
    {
        if ($date = \DateTime::createFromFormat('Y-m-d', $date . '-01')) {
            return $date;
        }

        return null;
    }
}
