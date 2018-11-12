<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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
            ->addArgument('month', InputArgument::OPTIONAL, 'The month date as `Y-m-d`.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // Argument
        /** @var \DateTime $month */
        if (null === $month = $this->createDate($input->getArgument('month'))) {
            $question = new Question('Please enter the month date as `Y-m-d`: ');
            $question->setValidator(function ($answer) {
                if (null === $date = $this->createDate($answer)) {
                    throw new \RuntimeException('The `from` date should be formatted as `Y-m-d`');
                }

                return $date;
            });
            $question->setMaxAttempts(3);

            $month = $helper->ask($input, $output, $question);
        }

        // Confirmation
        $output->writeln('<error>This is a dangerous operation.</error>');
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Recalculate invoices of {$month->format('Y-m-d')} ?", false
        );
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        // Recalculation
        $count = 0;
        $invoices = $this->invoiceRepository->findByMonth($month);
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
                $output->writeln('<error>error</error>');
                $this->invoiceManager->detach($invoice);

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
        if ($date = \DateTime::createFromFormat('Y-m-d', $date)) {
            return $date;
        }

        return null;
    }
}
