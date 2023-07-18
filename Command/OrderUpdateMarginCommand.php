<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Order\Manager\OrderManagerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Updater\OrderUpdaterInterface;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function gc_collect_cycles;
use function sprintf;

/**
 * Class OrderUpdateMarginCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderUpdateMarginCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:order:update:margin';
    protected static $defaultDescription = 'Updates the orders margins.';

    private int $id = 0;

    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly OrderUpdaterInterface    $updater,
        private readonly OrderManagerInterface    $manager,
        private readonly EntityManagerInterface   $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The single order id.')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The order id to start from.')
            ->addOption('month', null, InputOption::VALUE_REQUIRED, 'The month date as `Y-m`.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return iterable<int, OrderInvoiceInterface>
     */
    protected function getOrders(InputInterface $input, OutputInterface $output): iterable
    {
        if (null !== $id = $input->getOption('id')) {
            if (null === $invoice = $this->repository->find((int)$id)) {
                $output->writeln("<error>Order #$id not found</error>");

                return [];
            }

            return [$invoice];
        }

        if (null !== $month = $input->getOption('month')) {
            if (false === $date = DateTime::createFromFormat('Y-m-d', $month . '-01')) {
                $output->writeln("<error>Failed to parse '$month'</error>");

                return [];
            }

            return $this->repository->findByMonth($date);
        }

        $this->id = (int)$input->getOption('from');

        $output->writeln('<comment>Updating all orders</comment>');

        return $this->findNextOrder();
    }

    protected function findNextOrder(): Generator
    {
        $class = $this->repository->getClassName();

        $query = $this->entityManager->createQuery(<<<DQL
            SELECT o
            FROM $class o 
            WHERE o.sample = :sample 
              AND o.id > :id 
            ORDER BY o.id ASC
            DQL
        )->setMaxResults(1);

        $query->setParameters(['sample' => false, 'id' => $this->id]);

        while (null !== $order = $query->getOneOrNullResult()) {
            $this->id = $order->getId();

            yield $order;

            $query->setParameters(['sample' => false, 'id' => $this->id]);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->getOrders($input, $output);

        if (empty($orders)) {
            return Command::FAILURE;
        }

        // Confirmations
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to continue ?', false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        $showDiff = function (array $diff) use ($output) {
            $output->writeln('');
            $output->writeln('  Diffs:');

            foreach ($diff as $key => $amounts) {
                $output->writeln(sprintf('   * %s : %s => %s', $key, $amounts[0], $amounts[1]));
            }
        };

        // Recalculation
        $count = $updated = 0;
        foreach ($orders as $order) {
            $count++;
            $output->write(sprintf(
                '<comment>[%d] %s</comment> ... ',
                $order->getId(),
                $order->getNumber()
            ));

            $old = $this->toArray(clone $order->getMargin());

            if (!$this->updater->updateMargin($order)) {
                $output->writeln('<comment>up to date</comment>');

                goto loop;
            }

            $new = $this->toArray($order->getMargin());

            $showDiff($this->getDiff($old, $new));

            $this->manager->updateMargin($order);

            $updated++;

            $output->writeln('<info>updated</info>');

            loop:
            if (0 === $count % 20) {
                $this->entityManager->clear();
                gc_collect_cycles();
            }
        }

        $output->writeln(sprintf('<info>Updated %d order</info>', $updated));

        return Command::SUCCESS;
    }

    private function toArray(Margin $margin): array
    {
        return [
            'revenueProduct'  => $margin->getRevenueProduct(),
            'revenueShipment' => $margin->getRevenueShipment(),
            'costProduct'     => $margin->getCostProduct(),
            'costSupply'      => $margin->getCostSupply(),
            'costShipment'    => $margin->getCostShipment(),
        ];
    }

    private function getDiff(array $a, array $b): array
    {
        $decimalDiff = function (Decimal $a, Decimal $b): ?array {
            if ($a->equals($b)) {
                return null;
            }

            return [$a->toFixed(5), $b->toFixed(5)];
        };

        $keys = [
            'revenueProduct',
            'revenueShipment',
            'costProduct',
            'costSupply',
            'costShipment',
        ];

        $diff = [];

        foreach ($keys as $key) {
            if (null !== $d = $decimalDiff($a[$key], $b[$key])) {
                $diff[$key] = $d;
            }
        }

        return $diff;
    }
}

