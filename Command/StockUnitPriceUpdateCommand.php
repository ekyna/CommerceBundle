<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StockUnitPriceUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitPriceUpdateCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:stock-unit:price-update';
    protected static $defaultDescription = 'Updates the stock units prices';

    public function __construct(
        private readonly SupplierOrderItemCalculatorInterface $calculator,
        private readonly EntityManagerInterface               $manager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
            $id = $unit->getId();
            $item = $unit->getSupplierOrderItem();

            $output->write('Unit #'.$id.':');

            $changed = false;

            $netPrice = $this->calculator->calculateItemProductPrice($item);
            if (!$unit->getNetPrice()->equals($netPrice)) {
                $unit->setNetPrice($netPrice);
                $changed = true;
                $output->write(' <info>price</info>');
            } else {
                $output->write(' <comment>price</comment>');
            }

            $shippingPrice = $this->calculator->calculateItemShippingPrice($item);
            if (!$unit->getShippingPrice()->equals($shippingPrice)) {
                $unit->setShippingPrice($shippingPrice);
                $changed = true;
                $output->writeln(' <info>shipping</info>');
            } else {
                $output->writeln(' <comment>shipping</comment>');
            }

            if ($changed) {
                $this->manager->persist($unit);
            }

            if ($count % 20 === 0) {
                $this->manager->flush();
                $this->manager->clear();
            }
        }

        $this->manager->flush();
        $this->manager->clear();

        return Command::SUCCESS;
    }
}
