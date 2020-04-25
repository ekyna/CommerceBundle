<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
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
    protected static $defaultName = 'ekyna:commerce:stock-unit:price-update';

    /**
     * @var SupplierOrderCalculatorInterface
     */
    private $calculator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param SupplierOrderCalculatorInterface $calculator
     * @param EntityManagerInterface           $manager
     */
    public function __construct(
        SupplierOrderCalculatorInterface $calculator,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->calculator = $calculator;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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

            $output->write('Unit #' . $id . ':');

            $changed = false;

            $netPrice = $this->calculator->calculateStockUnitNetPrice($item);
            if ($netPrice !== $unit->getNetPrice()) {
                $unit->setNetPrice($netPrice);
                $changed = true;
                $output->write(' <info>price</info>');
            } else {
                $output->write(' <comment>price</comment>');
            }

            $shippingPrice = $this->calculator->calculateStockUnitShippingPrice($item);
            if ($shippingPrice !== $unit->getShippingPrice()) {
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
    }
}
