<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Stat\Updater\StatUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdateCommand extends ContainerAwareCommand
{
    /**
     * @var StatUpdaterInterface
     */
    private $updater;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var bool
     */
    private $flush;


    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:stat:update')
            ->setDescription('Updates the statistics')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Whether to force the order statistics update.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updater = $this->getContainer()->get('ekyna_commerce.stat.updater');

        $this->force = $input->getOption('force');
        $this->flush = false;

        $this->updateStockStat($output);

        $this->updateOrderStat($output);

        if ($this->flush) {
            $this->getContainer()->get('doctrine.orm.default_entity_manager')->flush();
        }
    }

    /**
     * Updates the stock stats.
     *
     * @param OutputInterface $output
     */
    private function updateStockStat(OutputInterface $output)
    {
        $name = 'Stock';
        $output->write(sprintf(
            '- %s %s ',
            $name,
            str_pad('.', 32 - mb_strlen($name), '.', STR_PAD_LEFT)
        ));

        if ($this->updater->updateStockStat()) {
            $output->writeln('<info>created</info>');

            $this->flush = true;

            return;
        }

        $output->writeln('<comment>exists</comment>');
    }

    /**
     * Updates the order stats.
     *
     * @param OutputInterface $output
     */
    private function updateOrderStat(OutputInterface $output)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        $orderDates = $statDates = $updatedMonths = $updatedYears = [];

        /** ---------------------------- Day stats ---------------------------- */

        $result = $connection->query(
            'SELECT DATE(o.created_at) AS date, MAX(o.updated_at) AS updated FROM commerce_order AS o GROUP BY date'
        );
        while (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $orderDates[$data['date']] = $data['updated'];
        }

        $result = $connection->query(
            'SELECT s.date, s.updated_at as updated FROM commerce_stat_order AS s ORDER BY s.date ASC'
        );
        while (false !== $data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $statDates[$data['date']] = $data['updated'];
        }

        $updatedMonths = [];

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

            $d = new \DateTime($date);
            if ($this->updater->updateDayOrderStat($d, $this->force)) {
                $output->writeln('<info>updated</info>');

                $month = $d->format('Y-m');
                if (!in_array($month, $updatedMonths, true)) {
                    $updatedMonths[] = $month;
                }

                $this->flush = true;
                continue;
            }

            $output->writeln('<comment>up to date</comment>');
        }

        /** ---------------------------- Month stats ---------------------------- */

        foreach ($updatedMonths as $month) {
            $output->write(sprintf(
                '- %s %s ',
                $month,
                str_pad('.', 32 - mb_strlen($month), '.', STR_PAD_LEFT)
            ));

            $d = new \DateTime($month . '-01');
            if ($this->updater->updateMonthOrderStat($d, $this->force)) {
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

            $d = new \DateTime($year . '-01-01');
            if ($this->updater->updateYearOrderStat($d, $this->force)) {
                $output->writeln('<info>updated</info>');

                $this->flush = true;
                continue;
            }

            $output->writeln('<comment>up to date</comment>');
        }
    }
}