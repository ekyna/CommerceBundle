<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;
use Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface;
use Ekyna\Component\Commerce\Stat\Updater\AbstractStockStatUpdater;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;

use function get_class;

/**
 * Class StockStatUpdater
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockStatUpdater extends AbstractStockStatUpdater
{
    private ?StockStatRepositoryInterface $statRepository = null;

    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
    }

    protected function calculateStockStats(): array
    {
        $qb = $this
            ->registry
            ->getManagerForClass(AbstractStockUnit::class)
            ->createQueryBuilder();

        $ex = $qb->expr();

        return $qb
            ->select([
                'SUM((u.receivedQuantity + u.adjustedQuantity - u.shippedQuantity) * u.netPrice) as in_value',
                'SUM((u.soldQuantity - u.shippedQuantity) * u.netPrice) as sold_value',
            ])
            ->from(AbstractStockUnit::class, 'u')
            ->andWhere($ex->in('u.state', ':state'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter(':state', [StockUnitStates::STATE_PENDING, StockUnitStates::STATE_READY])
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
    }

    protected function getStatRepository(): StockStatRepositoryInterface
    {
        if (null !== $this->statRepository) {
            return $this->statRepository;
        }

        return $this->statRepository = $this->registry->getRepository(StockStat::class);
    }

    protected function persist(object $object): void
    {
        $this->registry->getManagerForClass(get_class($object))->persist($object);
    }
}
