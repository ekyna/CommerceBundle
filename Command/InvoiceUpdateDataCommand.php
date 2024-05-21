<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class InvoiceUpdateDataCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceUpdateDataCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:invoice:update:data';
    protected static $defaultDescription = 'Updates the invoice data.';

    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly LockChecker                     $lockChecker,
        private readonly InvoiceBuilderInterface         $invoiceBuilder,
        private readonly EntityManagerInterface          $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'The invoice id.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Whether to force update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        if (null === $invoice = $this->repository->find((int)$id)) {
            $output->writeln("<error>Invoice #$id not found</error>");

            return Command::FAILURE;
        }

        // Confirmations
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to continue ?', false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        if ($input->getOption('force')) {
            $this->lockChecker->setEnabled(false);
        }

        // Update data
        $this->invoiceBuilder->update($invoice);

        // Persist
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        $output->writeln('<info>Invoice updated.</info>');

        return Command::SUCCESS;
    }
}

