<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Notify\NotifyQueue;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class OrderUpdateTotalsCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderUpdateTotalsCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:order:update-totals';

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var SaleUpdaterInterface
     */
    private $saleUpdater;

    /**
     * @var NotifyQueue
     */
    private $notifyQueue;

    /**
     * @var string
     */
    private $orderClass;

    /**
     * @var Query
     */
    private $nextOrderQuery;

    /**
     * @var int
     */
    private $lastId;

    /**
     * @var bool
     */
    private $amounts;

    /**
     * @var bool
     */
    private $payments;

    /**
     * @var bool
     */
    private $invoices;

    /**
     * @var int
     */
    private $totalCount = 0;

    /**
     * @var int
     */
    private $updatedCount = 0;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     * @param SaleUpdaterInterface   $saleUpdater
     * @param NotifyQueue            $notifyQueue
     * @param string                 $orderClass
     */
    public function __construct(
        EntityManagerInterface $manager,
        SaleUpdaterInterface $saleUpdater,
        NotifyQueue $notifyQueue,
        string $orderClass
    ) {
        parent::__construct();

        $this->manager     = $manager;
        $this->saleUpdater = $saleUpdater;
        $this->notifyQueue = $notifyQueue;
        $this->orderClass  = $orderClass;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription("Recalculates and updates order totals.")
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Update single order having this ID')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Update orders starting to this ID', 0)
            ->addOption('amounts', 'a', InputOption::VALUE_NONE, 'To update only amounts totals')
            ->addOption('payments', 'p', InputOption::VALUE_NONE, 'To update only payments totals')
            ->addOption('invoices', 'i', InputOption::VALUE_NONE, 'To update only invoices totals');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->amounts  = (bool)$input->getOption('amounts');
        $this->payments = (bool)$input->getOption('payments');
        $this->invoices = (bool)$input->getOption('invoices');

        if (!($this->amounts || $this->payments || $this->invoices)) {
            $this->amounts  = true;
            $this->payments = true;
            $this->invoices = true;
        }

        $operations = [];
        if ($this->amounts) {
            $operations[] = 'amounts';
        }
        if ($this->payments) {
            $operations[] = 'payments';
        }
        if ($this->invoices) {
            $operations[] = 'invoices';
        }

        $output->writeln("Updating " . implode(", ", $operations) . ".");

        $output->writeln('<error>This is a dangerous operation.</error>');

        if (0 < $id = (int)$input->getOption('id')) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("Recalculate and update order with id $id ?", false);
            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }

            if ($order = $this->findOrder($id)) {
                $this->updateOrder($order, $output);
            } else {
                $output->writeln("Order #$id not found");

                return 1;
            }

            return 0;
        }

        $this->lastId = (int)$input->getOption('from');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion("Recalculate and update orders from id $id ?", false);
        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        while ($order = $this->findNextOrder()) {
            $this->updateOrder($order, $output);
        }

        $output->writeln("\nUpdated $this->updatedCount / $this->totalCount\n");

        return 0;
    }

    private function updateOrder(OrderInterface $order, OutputInterface $output): void
    {
        $this->totalCount++;

        $this->lastId = $order->getId();

        $output->write(sprintf(
            '<comment>[%d] %s</comment> ... ',
            $order->getId(),
            $order->getNumber()
        ));

        $changed = false;

        try {
            if ($this->amounts) {
                $changed |= $this->saleUpdater->updateAmountTotals($order);
            }
            if ($this->payments) {
                $changed |= $this->saleUpdater->updatePaymentTotals($order);
            }
            if ($this->invoices) {
                $changed |= $this->saleUpdater->updateInvoiceTotals($order);
            }

            if ($changed) {
                $this->manager->persist($order);
                $this->manager->flush();

                $output->writeln('<info>done</info>');
                $this->updatedCount++;
            } else {
                $output->writeln('<comment>skipped</comment>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>error</error>');
        } catch (\Throwable $e) {
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
