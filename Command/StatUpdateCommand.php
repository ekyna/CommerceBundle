<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Ekyna\Component\Commerce\Stat\Updater\StatUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:stat:update';

    private StatUpdaterInterface     $statUpdater;
    private OrderUpdaterInterface    $orderUpdater;
    private OrderRepositoryInterface $orderRepository;
    private EntityManagerInterface   $manager;
    private string                   $orderClass;

    private bool $force;
    private bool $flush;
    private bool $debug;

    public function __construct(
        StatUpdaterInterface     $statUpdater,
        OrderUpdaterInterface    $orderUpdater,
        OrderRepositoryInterface $orderRepository,
        EntityManagerInterface   $manager,
        string                   $orderClass
    ) {
        parent::__construct();

        $this->statUpdater = $statUpdater;
        $this->orderUpdater = $orderUpdater;
        $this->orderRepository = $orderRepository;
        $this->manager = $manager;
        $this->orderClass = $orderClass;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the statistics')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Whether to force the order statistics update.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->debug = !$input->getOption('no-debug');
        $this->force = (bool)$input->getOption('force');
        $this->flush = false;

        $this->manager->getConnection()->getConfiguration()->setSQLLogger();

        $this->updateStockStat($output);

        $this->updateOrders($output);

        $this->updateOrderStat($output);

        if ($this->flush) {
            $this->manager->flush();
        }

        return Command::SUCCESS;
    }

    /**
     * Updates the stock stats.
     */
    private function updateStockStat(OutputInterface $output): void
    {
        $name = 'Stock';
        $this->debug && $output->write(sprintf(
            '- %s %s ',
            $name,
            str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        if ($this->statUpdater->updateStockStat()) {
            $this->debug && $output->writeln("<info>created</info>\n");

            $this->flush = true;

            return;
        }

        $this->debug && $output->writeln("<comment>up-to-date</comment>\n");
    }

    /**
     * Updates the orders revenue and margin totals.
     */
    private function updateOrders(OutputInterface $output): void
    {
        $this->debug && $output->writeln('Updating orders margin total');

        if (empty($ids = $this->orderRepository->findWithNullRevenueOrMargin())) {
            $this->debug && $output->writeln("<comment>all up-to-date</comment>\n");

            return;
        }

        $qb = $this->manager->createQueryBuilder();
        $update = $qb
            ->update($this->orderClass, 'o')
            ->set('o.revenueTotal', ':revenue')
            ->set('o.marginTotal', ':margin')
            ->set('o.updatedAt', ':date')
            ->where($qb->expr()->eq('o.id', ':id'))
            ->getQuery()
            ->setMaxResults(1);

        foreach ($ids as $id) {
            /** @var OrderInterface $order */
            if (!$order = $this->orderRepository->find($id)) {
                continue;
            }

            $name = $order->getNumber();
            $this->debug && $output->write(sprintf(
                '- %s %s ',
                $name,
                str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if (!$this->orderUpdater->updateMarginTotals($order)) {
                $this->debug && $output->writeln('<comment>up-to-date</comment>');
                continue;
            }

            $update
                ->setParameter('revenue', $order->getRevenueTotal())
                ->setParameter('margin', $order->getMarginTotal())
                ->setParameter('date', new DateTime(), Types::DATETIME_MUTABLE)
                ->setParameter('id', $id)
                ->execute();

            $this->manager->clear();

            $this->debug && $output->writeln('<info>updated</info>');
        }
    }

    /**
     * Updates the order stats.
     */
    private function updateOrderStat(OutputInterface $output): void
    {
        $connection = $this->manager->getConnection();

        $orderDates = $statDates = $updatedMonths = $updatedYears = [];

        /** ---------------------------- Day stats ---------------------------- */

        /** @noinspection SqlDialectInspection */
        $result = $connection->executeQuery(
            'SELECT DATE(o.created_at) AS date, MAX(o.updated_at) AS updated FROM commerce_order AS o GROUP BY date'
        );
        while (false !== $data = $result->fetchAssociative()) {
            $orderDates[$data['date']] = $data['updated'];
        }

        /** @noinspection SqlDialectInspection */
        $result = $connection->executeQuery(
            'SELECT s.date, s.updated_at as updated FROM commerce_stat_order AS s ORDER BY s.date'
        );
        while (false !== $data = $result->fetchAssociative()) {
            $statDates[$data['date']] = $data['updated'];
        }

        foreach ($orderDates as $date => $updated) {
            $name = $date;
            $this->debug && $output->write(sprintf(
                '- %s %s ',
                $name,
                str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if (!$this->force && isset($statDates[$date]) && $statDates[$date] > $updated) {
                $this->debug && $output->writeln('<comment>skipped</comment>');
                continue;
            }

            $d = new DateTime($date);
            if ($this->statUpdater->updateDayOrderStat($d, $this->force)) {
                $this->debug && $output->writeln('<info>updated</info>');

                $month = $d->format('Y-m');
                if (!in_array($month, $updatedMonths, true)) {
                    $updatedMonths[] = $month;
                }

                $this->flush = true;
                continue;
            }

            $this->debug && $output->writeln('<comment>up-to-date</comment>');
        }

        /** ---------------------------- Month stats ---------------------------- */

        foreach ($updatedMonths as $month) {
            $this->debug && $output->write(sprintf(
                '- %s %s ',
                $month,
                str_pad('.', 32 - mb_strlen($month), '.', STR_PAD_LEFT)
            ));

            $d = new DateTime($month . '-01');
            if ($this->statUpdater->updateMonthOrderStat($d, $this->force)) {
                $this->debug && $output->writeln('<info>updated</info>');

                $year = $d->format('Y');
                if (!in_array($year, $updatedYears, true)) {
                    $updatedYears[] = $year;
                }

                $this->flush = true;
                continue;
            }

            $this->debug && $output->writeln('<comment>up to date</comment>');
        }

        /** ---------------------------- Year stats ---------------------------- */

        foreach ($updatedYears as $year) {
            $this->debug && $output->write(sprintf(
                '- %s %s ',
                $year,
                str_pad('.', 32 - mb_strlen($year), '.', STR_PAD_LEFT)
            ));

            $d = new DateTime($year . '-01-01');
            if ($this->statUpdater->updateYearOrderStat($d, $this->force)) {
                $this->debug && $output->writeln('<info>updated</info>');

                $this->flush = true;
                continue;
            }

            $this->debug && $output->writeln('<comment>up to date</comment>');
        }
    }
}
