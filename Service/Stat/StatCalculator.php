<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Entity;
use Ekyna\Component\Commerce\Stat\Repository;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class StatCalculator
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCalculator implements StatCalculatorInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $orderClass;

    /**
     * @var \Ekyna\Component\Commerce\Stat\Repository\StockStatRepositoryInterface
     */
    protected $stockStatRepository;

    /**
     * @var \Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface
     */
    protected $orderStatRepository;

    /**
     * @var EntityRepository
     */
    protected $stockUnitRepository;

    /**
     * @var EntityRepository
     */
    protected $orderRepository;


    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     * @param string            $orderClass
     */
    public function __construct(RegistryInterface $registry, $orderClass)
    {
        $this->registry = $registry;
        $this->orderClass = $orderClass;
    }

    /**
     * @inheritdoc
     */
    public function calculateStockStats()
    {
        $qb = $this->getStockUnitRepository()->createQueryBuilder('u');
        $ex = $qb->expr();

        return $qb
            ->select([
                'SUM((u.receivedQuantity + u.adjustedQuantity - u.shippedQuantity) * u.netPrice) as in_value',
                'SUM((u.soldQuantity - u.shippedQuantity) * u.netPrice) as sold_value',
            ])
            ->andWhere($ex->in('u.state', ':state'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter(':state', [StockUnitStates::STATE_PENDING, StockUnitStates::STATE_READY])
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * @inheritdoc
     */
    public function calculateDayOrderStats(\DateTime $date)
    {
        $from = clone $date;
        $from->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to->setTime(23, 59, 59, 999999);

        return $this->calculateOrderStats($from, $to);
    }

    /**
     * @inheritdoc
     */
    public function calculateMonthOrderStats(\DateTime $date)
    {
        $from = clone $date;
        $from
            ->modify('first day of this month')
            ->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to
            ->modify('last day of this month')
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrderStats($from, $to);
    }

    /**
     * @inheritdoc
     */
    public function calculateYearOrderStats(\DateTime $date)
    {
        $from = clone $date;
        $from
            ->modify('first day of january ' . $date->format('Y'))
            ->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to
            ->modify('last day of december ' . $date->format('Y'))
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrderStats($from, $to);
    }

    /**
     * @inheritdoc
     */
    protected function calculateOrderStats(\DateTime $from, \DateTime $to)
    {
        $qb = $this->getOrderRepository()->createQueryBuilder('o');
        $ex = $qb->expr();

        $data = $qb
            ->select([
                'SUM(o.netTotal) as net',
                'SUM(o.shipmentAmount) as shipping',
                'SUM(o.marginTotal) as margin',
                'COUNT(o.id) as orders',
                'SUM(o.itemsCount) as items',
                'AVG(o.netTotal) as average',
            ])
            ->andWhere($ex->between('o.createdAt', ':from', ':to'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('o.state', ':state'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('from', $from, Type::DATETIME)
            ->setParameter('to', $to, Type::DATETIME)
            ->setParameter('sample', false)
            ->setParameter('state', [
                OrderStates::STATE_COMPLETED,
                OrderStates::STATE_ACCEPTED,
                OrderStates::STATE_PENDING,
            ])
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        if ($data) {
            $result = [
                'revenue'  => (string)round($data['net'] - $data['shipping'], 3),
                'shipping' => (string)round($data['shipping'], 3),
                'margin'   => (string)round($data['margin'], 3),
                'orders'   => (string)$data['orders'],
                'items'    => (string)$data['items'],
                'average'  => (string)round($data['average'], 3),
                'details'  => [],
            ];

            $qb = $this->getOrderRepository()->createQueryBuilder('o');
            $query = $qb
                ->select([
                    'SUM(o.netTotal) as net',
                    'SUM(o.shipmentAmount) as shipping',
                ])
                ->andWhere($ex->between('o.createdAt', ':from', ':to'))
                ->andWhere($ex->eq('o.sample', ':sample'))
                ->andWhere($ex->eq('o.source', ':source'))
                ->andWhere($ex->in('o.state', ':state'))
                ->getQuery()
                ->useQueryCache(true);

            foreach (SaleSources::getSources() as $source) {
                $data = $query
                    ->setParameter('from', $from, Type::DATETIME)
                    ->setParameter('to', $to, Type::DATETIME)
                    ->setParameter('source', $source)
                    ->setParameter('sample', false)
                    ->setParameter('state', [
                        OrderStates::STATE_COMPLETED,
                        OrderStates::STATE_ACCEPTED,
                        OrderStates::STATE_PENDING,
                    ])
                    ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

                if ($data) {
                    $result['details'][$source] = (string)round($data['net'] - $data['shipping'], 3);
                } else {
                    $result['details'][$source] = 0;
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * Returns the stock unit repository.
     *
     * @return EntityRepository
     */
    protected function getStockUnitRepository()
    {
        if (null !== $this->stockUnitRepository) {
            return $this->stockUnitRepository;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->stockUnitRepository = $this->registry->getRepository(AbstractStockUnit::class);
    }

    /**
     * Returns the order repository.
     *
     * @return EntityRepository
     */
    protected function getOrderRepository()
    {
        if (null !== $this->orderRepository) {
            return $this->orderRepository;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->orderRepository = $this->registry->getRepository($this->orderClass);
    }

    /**
     * Returns the order repository.
     *
     * @return Repository\StockStatRepositoryInterface
     */
    protected function getStockStatRepository()
    {
        if (null !== $this->stockStatRepository) {
            return $this->stockStatRepository;
        }

        return $this->stockStatRepository = $this->registry->getRepository(Entity\StockStat::class);
    }

    /**
     * Returns the order repository.
     *
     * @return Repository\OrderStatRepositoryInterface
     */
    protected function getOrderStatRepository()
    {
        if (null !== $this->orderStatRepository) {
            return $this->orderStatRepository;
        }

        return $this->orderStatRepository = $this->registry->getRepository(Entity\OrderStat::class);
    }
}
