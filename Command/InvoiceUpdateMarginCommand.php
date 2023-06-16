<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceMarginCalculatorFactory;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function date;
use function gc_collect_cycles;
use function sprintf;

/**
 * Class InvoiceUpdateMarginCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceUpdateMarginCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:invoice:update:margin';
    protected static $defaultDescription = 'Updates the invoices margins.';

    private int $id = 0;

    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly InvoiceMarginCalculatorFactory  $factory,
        private readonly EntityManagerInterface          $manager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The invoice id.')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The invoice id to start from.')
            ->addOption('month', 'm', InputOption::VALUE_REQUIRED, 'The month date as `Y-m`.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return array<int, OrderInvoiceInterface>
     */
    protected function getInvoices(InputInterface $input, OutputInterface $output): iterable
    {
        if (null !== $id = $input->getOption('id')) {
            if (null === $invoice = $this->repository->find((int)$id)) {
                $output->writeln("<error>Invoice #$id not found</error>");

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

        $output->writeln('<comment>Updating all invoices</comment>');

        return $this->findAll();
    }

    protected function findAll(): Generator
    {
        $class = $this->repository->getClassName();

        $query = $this->manager->createQuery(<<<DQL
            SELECT i
            FROM $class i 
            JOIN i.order o
            WHERE o.sample = :sample 
              AND i.id > :id 
            ORDER BY i.id ASC
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
        $invoices = $this->getInvoices($input, $output);

        if (empty($invoices)) {
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

        $class = $this->repository->getClassName();

        $update = $this->manager->createQuery(<<<DQL
            UPDATE $class i
            SET i.margin.revenueProduct = :revenue_product,
                i.margin.revenueShipment = :revenue_shipment,
                i.margin.costProduct = :cost_product,
                i.margin.costSupply = :cost_supply,
                i.margin.costShipment = :cost_shipment,
                i.margin.average = :is_average,
                i.updatedAt = :date
            WHERE i.id = :id
            DQL
        );

        // Recalculation
        $count = $updated = 0;
        foreach ($invoices as $invoice) {
            $count++;
            $output->write(sprintf(
                '<comment>[%s - %d] %s</comment> ... ',
                $invoice->getSale()->getId(),
                $invoice->getId(),
                $invoice->getNumber()
            ));

            $old = $this->toArray(clone $invoice->getMargin());

            $margin = $this
                ->factory
                ->create()
                ->calculateInvoice($invoice);

            $new = $this->toArray($margin);

            if (0 === $count % 20) {
                $this->manager->clear();
                gc_collect_cycles();
            }

            if (empty($diff = $this->getDiff($old, $new))) {
                $output->writeln('<comment>up to date</comment>');

                continue;
            }

            $showDiff($diff);

            $update->execute([
                'revenue_product'  => $margin->getRevenueProduct()->toFixed(5),
                'revenue_shipment' => $margin->getRevenueShipment()->toFixed(5),
                'cost_product'     => $margin->getCostProduct()->toFixed(5),
                'cost_supply'      => $margin->getCostSupply()->toFixed(5),
                'cost_shipment'    => $margin->getCostShipment()->toFixed(5),
                'is_average'       => $margin->isAverage() ? 1 : 0,
                'date'             => date('Y-m-d H:i:s'),
                'id'               => $invoice->getId(),
            ]);

            $updated++;

            $output->writeln('<info>updated</info>');
        }

        $output->writeln(sprintf('<info>Updated %d invoices</info>', $updated));

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

