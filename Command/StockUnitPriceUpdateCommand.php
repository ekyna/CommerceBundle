<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function implode;
use function sprintf;

/**
 * Class StockUnitPriceUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitPriceUpdateCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:stock-unit:price-update';
    protected static $defaultDescription = 'Updates the stock units prices';

    private SymfonyStyle $io;

    public function __construct(
        private readonly SupplierOrderItemCalculatorInterface $calculator,
        private readonly EntityManagerInterface               $manager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::OPTIONAL, 'The stock unit ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $id = $input->getArgument('id')) {
            if (null === $unit = $this->fetchSingle((int)$id)) {
                $this->io->writeln('<error>Stock unit not found (or not linked to a supplier order item)</error>');

                return Command::FAILURE;
            }

            if ($this->updateSingle($unit)) {
                $this->manager->flush();
                $this->manager->clear();
            }

            return Command::SUCCESS;
        }

        $this->updateAll();

        return Command::SUCCESS;
    }

    private function updateAll(): void
    {
        if (!$this->io->confirm('Do you want to continue ?')) {
            return;
        }

        $qb = $this->manager->createQueryBuilder();

        $query = $qb
            ->select('u')
            ->from(AbstractStockUnit::class, 'u')
            ->andWhere($qb->expr()->gt('u.id', ':id'))
            ->andWhere($qb->expr()->isNotNull('u.supplierOrderItem'))
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery();

        $count = $id = 0;

        /** @var AbstractStockUnit $unit */
        while (null !== $unit = $query->setParameters(['id' => $id])->getOneOrNullResult()) {
            $count++;

            $this->updateSingle($unit);

            if ($count % 20 === 0) {
                $this->manager->flush();
                $this->manager->clear();
            }
        }

        $this->manager->flush();
        $this->manager->clear();
    }

    private function fetchSingle(int $id): ?StockUnitInterface
    {
        $qb = $this->manager->createQueryBuilder();

        return $qb
            ->select('u')
            ->from(AbstractStockUnit::class, 'u')
            ->andWhere($qb->expr()->eq('u.id', ':id'))
            ->andWhere($qb->expr()->isNotNull('u.supplierOrderItem'))
            ->setMaxResults(1)
            ->getQuery()
            ->setParameter('id', $id)
            ->getOneOrNullResult();
    }

    private function updateSingle(StockUnitInterface $unit): bool
    {
        $id = $unit->getId();
        $item = $unit->getSupplierOrderItem();

        $this->io->write('Unit #' . $id . ':');

        $changed = false;
        $parts = [];

        $netPrice = $this->calculator->calculateItemProductPrice($item);
        if (!$unit->getNetPrice()->equals($netPrice)) {
            $changed = true;
            $parts[] = sprintf(
                '<info>price</info>: %s => %s',
                $unit->getNetPrice()->toFixed(5),
                $netPrice->toFixed(5)
            );
            $unit->setNetPrice($netPrice);
        } else {
            $parts[] = '<comment>price</comment>';
        }

        $shippingPrice = $this->calculator->calculateItemShippingPrice($item);
        if (!$unit->getShippingPrice()->equals($shippingPrice)) {
            $changed = true;
            $parts[] = sprintf(
                '<info>price</info>: %s => %s',
                $unit->getShippingPrice()->toFixed(5),
                $shippingPrice->toFixed(5)
            );
            $unit->setShippingPrice($shippingPrice);
        } else {
            $parts[] = '<comment>shipping</comment>';
        }

        if (!$changed) {
            $this->io->writeln(' ' . implode(' ', $parts));
        } else {
            $this->io->writeln('');
            foreach ($parts as $part) {
                $this->io->writeln(' - ' . $part);
            }
        }

        if ($changed) {
            $this->manager->persist($unit);

            return true;
        }

        return false;
    }
}
