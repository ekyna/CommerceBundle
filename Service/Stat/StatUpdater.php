<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Ekyna\Component\Commerce\Stat\Updater\AbstractStatUpdater;

/**
 * Class StatUpdater
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdater extends AbstractStatUpdater
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var \Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface
     */
    private $stockStatRepository;

    /**
     * @var \Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface
     */
    private $orderStatRepository;


    /**
     * Constructor.
     *
     * @param StatCalculatorInterface $calculator
     * @param EntityManagerInterface  $manager
     */
    public function __construct(StatCalculatorInterface $calculator, EntityManagerInterface $manager)
    {
        parent::__construct($calculator);

        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function persist($object)
    {
        $this->manager->persist($object);
    }

    /**
     * @inheritDoc
     */
    protected function getStockStatRepository()
    {
        if (null !== $this->stockStatRepository) {
            return $this->stockStatRepository;
        }

        return $this->stockStatRepository = $this->manager->getRepository(StockStat::class);
    }

    /**
     * @inheritDoc
     */
    protected function getOrderStatRepository()
    {
        if (null !== $this->orderStatRepository) {
            return $this->orderStatRepository;
        }

        return $this->orderStatRepository = $this->manager->getRepository(OrderStat::class);
    }
}