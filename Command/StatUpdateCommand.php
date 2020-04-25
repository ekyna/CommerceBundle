<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @var StatUpdaterInterface
     */
    private $statUpdater;

    /**
     * @var OrderUpdaterInterface
     */
    private $orderUpdater;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $orderClass;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var bool
     */
    private $flush;


    /**
     * Constructor.
     *
     * @param StatUpdaterInterface   $statUpdater
     * @param OrderUpdaterInterface  $orderUpdater
     * @param EntityManagerInterface $manager
     * @param string                 $orderClass
     */
    public function __construct(
        StatUpdaterInterface $statUpdater,
        OrderUpdaterInterface $orderUpdater,
        EntityManagerInterface $manager,
        string $orderClass
    ) {
        parent::__construct();

        $this->statUpdater = $statUpdater;
        $this->orderUpdater = $orderUpdater;
        $this->manager = $manager;
        $this->orderClass = $orderClass;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Updates the statistics')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Whether to force the order statistics update.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->force = $input->getOption('force');
        $this->flush = false;

        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->updateStockStat($output);

        $this->updateOrders($output);

        $this->updateOrderStat($output);

        if ($this->flush) {
            $this->manager->flush();
        }
    }

    /**
     * Updates the stock stats.
     *
     * @param OutputInterface $output
     */
    private function updateStockStat(OutputInterface $output): void
    {
        $name = 'Stock';
        $output->write(sprintf(
            '- %s %s ',
            $name,
            str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        if ($this->statUpdater->updateStockStat()) {
            $output->writeln("<info>created</info>\n");

            $this->flush = true;

            return;
        }

        $output->writeln("<comment>up-to-date</comment>\n");
    }

    /**
     * Updates the orders revenue and margin totals.
     *
     * @param OutputInterface $output
     */
    private function updateOrders(OutputInterface $output): void
    {
        $output->writeln('Updating orders margin total');

        /** @var \Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface $repository */
        $repository = $this->manager->getRepository($this->orderClass);

        if (empty($ids = $repository->findWithNullRevenueOrMargin())) {
            $output->writeln("<comment>all up-to-date</comment>\n");

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
            /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
            if (!$order = $repository->find($id)) {
                continue;
            }

            $name = $order->getNumber();
            $output->write(sprintf(
                '- %s %s ',
                $name,
                str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if (!$this->orderUpdater->updateMarginTotals($order)) {
                $output->writeln("<comment>up-to-date</comment>");
                continue;
            }

            $update
                ->setParameter('revenue', $order->getRevenueTotal())
                ->setParameter('margin', $order->getMarginTotal())
                ->setParameter('date', new DateTime(), Types::DATETIME_MUTABLE)
                ->setParameter('id', $id)
                ->execute();

            $this->manager->clear();

            $output->writeln("<info>updated</info>");
        }
    }

    /**
     * Updates the order stats.
     *
     * @param OutputInterface $output
     */
    private function updateOrderStat(OutputInterface $output): void
    {
        $connection = $this->manager->getConnection();

        $orderDates = $statDates = $updatedMonths = $updatedYears = [];

        /** ---------------------------- Day stats ---------------------------- */

        $result = $connection->query(
            'SELECT DATE(o.created_at) AS date, MAX(o.updated_at) AS updated FROM commerce_order AS o GROUP BY date'
        );
        while (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $orderDates[$data['date']] = $data['updated'];
        }

        $result = $connection->query(
            'SELECT s.date, s.updated_at as updated FROM commerce_stat_order AS s ORDER BY s.date'
        );
        while (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $statDates[$data['date']] = $data['updated'];
        }

        foreach ($orderDates as $date => $updated) {
            $name = $date;
            $output->write(sprintf(
                '- %s %s ',
                $name,
                str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if (!$this->force && isset($statDates[$date]) && $statDates[$date] > $updated) {
                $output->writeln('<comment>skipped</comment>');
                continue;
            }

            $d = new DateTime($date);
            if ($this->statUpdater->updateDayOrderStat($d, $this->force)) {
                $output->writeln('<info>updated</info>');

                $month = $d->format('Y-m');
                if (!in_array($month, $updatedMonths, true)) {
                    $updatedMonths[] = $month;
                }

                $this->flush = true;
                continue;
            }

            $output->writeln('<comment>up-to-date</comment>');
        }

        /** ---------------------------- Month stats ---------------------------- */

        foreach ($updatedMonths as $month) {
            $output->write(sprintf(
                '- %s %s ',
                $month,
                str_pad('.', 32 - mb_strlen($month), '.', STR_PAD_LEFT)
            ));

            $d = new DateTime($month . '-01');
            if ($this->statUpdater->updateMonthOrderStat($d, $this->force)) {
                $output->writeln('<info>updated</info>');

                $year = $d->format('Y');
                if (!in_array($year, $updatedYears, true)) {
                    $updatedYears[] = $year;
                }

                $this->flush = true;
                continue;
            }

            $output->writeln('<comment>up to date</comment>');
        }

        /** ---------------------------- Year stats ---------------------------- */

        foreach ($updatedYears as $year) {
            $output->write(sprintf(
                '- %s %s ',
                $year,
                str_pad('.', 32 - mb_strlen($year), '.', STR_PAD_LEFT)
            ));

            $d = new DateTime($year . '-01-01');
            if ($this->statUpdater->updateYearOrderStat($d, $this->force)) {
                $output->writeln('<info>updated</info>');

                $this->flush = true;
                continue;
            }

            $output->writeln('<comment>up to date</comment>');
        }
    }
}
