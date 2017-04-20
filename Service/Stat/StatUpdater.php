<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Ekyna\Component\Commerce\Stat\Repository;
use Ekyna\Component\Commerce\Stat\Updater\AbstractStatUpdater;

use function get_class;

/**
 * Class StatUpdater
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatUpdater extends AbstractStatUpdater
{
    private ManagerRegistry $registry;

    private ?Repository\StockStatRepositoryInterface $stockStatRepository = null;
    private ?Repository\OrderStatRepositoryInterface $orderStatRepository = null;


    public function __construct(StatCalculatorInterface $calculator, ManagerRegistry $manager)
    {
        parent::__construct($calculator);

        $this->registry = $manager;
    }

    protected function persist(object $object): void
    {
        $this->registry->getManagerForClass(get_class($object))->persist($object);
    }

    protected function getStockStatRepository(): Repository\StockStatRepositoryInterface
    {
        if (null !== $this->stockStatRepository) {
            return $this->stockStatRepository;
        }

        return $this->stockStatRepository = $this->registry->getRepository(StockStat::class);
    }

    protected function getOrderStatRepository(): Repository\OrderStatRepositoryInterface
    {
        if (null !== $this->orderStatRepository) {
            return $this->orderStatRepository;
        }

        return $this->orderStatRepository = $this->registry->getRepository(OrderStat::class);
    }
}
