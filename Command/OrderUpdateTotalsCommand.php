<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

/**
 * Class OrderUpdateTotalsCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderUpdateTotalsCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:order:update-totals';

    private EntityManagerInterface $manager;
    private SaleUpdaterInterface   $saleUpdater;
    private OrderUpdaterInterface  $orderUpdater;
    private NotifyQueue            $notifyQueue;
    private string                 $orderClass;

    private Query $nextOrderQuery;
    private int   $lastId;
    private bool  $amount;
    private bool  $payment;
    private bool  $invoice;
    private bool  $margin;
    private bool  $dryRun;
    private int   $totalCount   = 0;
    private int   $updatedCount = 0;


    public function __construct(
        EntityManagerInterface $manager,
        SaleUpdaterInterface   $saleUpdater,
        OrderUpdaterInterface  $orderUpdater,
        NotifyQueue            $notifyQueue,
        string                 $orderClass
    ) {
        parent::__construct();

        $this->manager = $manager;
        $this->saleUpdater = $saleUpdater;
        $this->orderUpdater = $orderUpdater;
        $this->notifyQueue = $notifyQueue;
        $this->orderClass = $orderClass;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Recalculates and updates order totals.')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Update single order having this ID')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Update orders starting to this ID', 0)
            ->addOption('amount', 'a', InputOption::VALUE_NONE, 'To update only amount totals')
            ->addOption('payment', 'p', InputOption::VALUE_NONE, 'To update only payment totals')
            ->addOption('invoice', 'i', InputOption::VALUE_NONE, 'To update only invoice totals')
            ->addOption('margin', 'm', InputOption::VALUE_NONE, 'To update only margin totals')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'To not persist');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->amount = (bool)$input->getOption('amount');
        $this->payment = (bool)$input->getOption('payment');
        $this->invoice = (bool)$input->getOption('invoice');
        $this->margin = (bool)$input->getOption('margin');
        $this->dryRun = (bool)$input->getOption('dry-run');

        if (!($this->amount || $this->payment || $this->invoice || $this->margin)) {
            $this->amount = true;
            $this->payment = true;
            $this->invoice = true;
            $this->margin = true;
        }

        $operations = [];
        if ($this->amount) {
            $operations[] = 'amount';
        }
        if ($this->payment) {
            $operations[] = 'payment';
        }
        if ($this->invoice) {
            $operations[] = 'invoice';
        }
        if ($this->margin) {
            $operations[] = 'margin';
        }
        if ($this->dryRun) {
            $operations[] = '(dry run)';
        }

        $output->writeln('Updating ' . implode(', ', $operations) . '.');

        $output->writeln('<error>This is a dangerous operation.</error>');

        if (0 < $id = (int)$input->getOption('id')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("Recalculate and update order with id $id ?", false);
            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }

            if ($order = $this->findOrder($id)) {
                $this->updateOrder($order, $output);
            } else {
                $output->writeln("Order #$id not found");

                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        $this->lastId = (int)$input->getOption('from');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Recalculate and update orders from id $this->lastId ?", false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        while ($order = $this->findNextOrder()) {
            $this->updateOrder($order, $output);
        }

        $output->writeln("\nUpdated $this->updatedCount / $this->totalCount\n");

        return Command::SUCCESS;
    }

    private function updateOrder(OrderInterface $order, OutputInterface $output): void
    {
        $this->totalCount++;

        $this->lastId = $order->getId() ?? 0;

        $output->write(sprintf(
            '<comment>[%d] %s</comment> ... ',
            $order->getId(),
            $order->getNumber()
        ));

        $changed = false;

        try {
            if ($this->amount) {
                $changed = $this->saleUpdater->updateAmountTotals($order) || $changed;
            }
            if ($this->payment) {
                $changed = $this->saleUpdater->updatePaymentTotals($order) || $changed;
            }
            if ($this->invoice) {
                $changed = $this->saleUpdater->updateInvoiceTotals($order) || $changed;
            }
            if ($this->margin) {
                $changed = $this->orderUpdater->updateMarginTotals($order) || $changed;
            }

            if ($changed) {
                if (!$this->dryRun) {
                    $order->setUpdatedAt(new DateTime());

                    $this->manager->persist($order);
                    $this->manager->flush();
                }

                $output->writeln('<info>updated</info>');
                $this->updatedCount++;
            } else {
                $output->writeln('<comment>skipped</comment>');
            }

            if ($this->totalCount % 20 === 0) {
                $this->manager->clear();
            }
        } catch (Throwable $e) {
            $output->writeln('<error>error</error>');
        }

        $this->manager->clear();

        // Prevent automatic notifications
        $this->notifyQueue->clear();
    }

    private function findOrder(int $id): ?OrderInterface
    {
        $qb = $this->manager->createQueryBuilder();

        return $qb
            ->select('o')
            ->from($this->orderClass, 'o')
            ->andWhere($qb->expr()->eq('o.id', ':id'))
            ->getQuery()
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    private function findNextOrder(): ?OrderInterface
    {
        if (!$this->nextOrderQuery) {
            $qb = $this->manager->createQueryBuilder();

            $this->nextOrderQuery = $qb
                ->select('o')
                ->from($this->orderClass, 'o')
                ->andWhere($qb->expr()->gt('o.id', ':last_id'))
                ->getQuery()
                ->setMaxResults(1);
        }

        return $this->nextOrderQuery
            ->setParameter('last_id', $this->lastId)
            ->getOneOrNullResult();
    }
}
