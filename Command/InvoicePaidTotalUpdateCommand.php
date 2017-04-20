<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class InvoicePaidTotalUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaidTotalUpdateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:invoice:update-paid-total';

    private InvoicePaymentResolverInterface $resolver;
    private EntityManagerInterface          $manager;
    private string                          $invoiceClass;

    private Statement $updateTotal;
    private Statement $updateRealTotal;


    public function __construct(
        InvoicePaymentResolverInterface $resolver,
        EntityManagerInterface          $manager,
        string                          $invoiceClass
    ) {
        parent::__construct();

        $this->resolver = $resolver;
        $this->manager = $manager;
        $this->invoiceClass = $invoiceClass;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the invoices paid total')
            ->addArgument('id', InputArgument::OPTIONAL, 'To update a single order\'s invoices', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Updating invoices due dates');
        $output->writeln('');

        $orderId = (int)$input->getArgument('id');

        // Confirmation
        $output->writeln('<error>This is a dangerous operation.</error>');
        $orders = $orderId ? "order#$orderId" : "all orders";
        $question = new ConfirmationQuestion("Update $orders invoices totals ?", false);
        $helper = $this->getHelper('question');
        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $table = $this->metadata->getTableName();

        /** @noinspection SqlResolve */
        $this->updateTotal = $this->manager->getConnection()->prepare(
            "UPDATE $table SET paid_total=:total WHERE id=:id LIMIT 1"
        );
        /** @noinspection SqlResolve */
        $this->updateRealTotal = $this->manager->getConnection()->prepare(
            "UPDATE $table SET real_paid_total=:total WHERE id=:id LIMIT 1"
        );

        $qb = $this->manager->createQueryBuilder()->from($metadata->getName(), 'i');

        // Single order invoices case
        if (0 < $orderId) {
            $invoices = $qb
                ->andWhere($qb->expr()->eq('IDENTITY(i.order)', ':order_id'))
                ->addOrderBy('i.id', 'ASC')
                ->getQuery()
                ->setParameter('order_id', $orderId)
                ->getResult();

            $this->updateInvoices($invoices, $output);

            $output->writeln('');

            return 0;
        }

        // All invoices case
        $limit = 30;
        $page = 0;

        $select = $qb
            ->addOrderBy('i.id', 'ASC')
            ->getQuery()
            ->setMaxResults($limit);

        do {
            $invoices = $select->setFirstResult($page * $limit)->execute();
            if (empty($invoices)) {
                break;
            }

            $this->updateInvoices($invoices, $output);

            $page++;
        } while (true);

        $output->writeln('');

        return 0;
    }

    /**
     * @param InvoiceInterface[] $invoices
     */
    private function updateInvoices(array $invoices, OutputInterface $output): void
    {
        foreach ($invoices as $invoice) {
            $number = $invoice->getNumber();

            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $number,
                str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
            ));

            $done = false;
            $data = [];

            $total = $this->resolver->getPaidTotal($invoice);
            if (!$invoice->getPaidTotal()->equals($total)) {
                $data['Paid total'] = [$invoice->getPaidTotal(), $total];
                if (!$this->updateTotal->execute(['total' => $total, 'id' => $invoice->getId()])) {
                    $output->writeln('<error>failure</error>');
                    continue;
                }
                $done = true;
            }

            $total = $this->resolver->getRealPaidTotal($invoice);
            if (!$invoice->getRealPaidTotal()->equals($total)) {
                $data['Real paid total'] = [$invoice->getRealPaidTotal(), $total];
                if (!$this->updateRealTotal->execute(['total' => $total, 'id' => $invoice->getId()])) {
                    $output->writeln('<error>failure</error>');
                    continue;
                }
                $done = true;
            }

            if ($done) {
                $output->writeln('<info>done</info>');

                foreach ($data as $property => [$old, $new]) {
                    $output->writeln("   - $property : $old => $new");
                }

                continue;
            }

            $output->writeln('<comment>skipped</comment>');
        }

        $this->manager->clear();
    }
}
