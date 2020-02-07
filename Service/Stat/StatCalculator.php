<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
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
    public function __construct(RegistryInterface $registry, string $orderClass)
    {
        $this->registry   = $registry;
        $this->orderClass = $orderClass;
    }

    /**
     * @inheritdoc
     */
    public function calculateStockStats(): array
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
    public function calculateDayOrderStats(\DateTime $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to->setTime(23, 59, 59, 999999);

        return $this->calculateOrderStats($from, $to, $filter);
    }

    /**
     * @inheritdoc
     */
    public function calculateMonthOrderStats(\DateTime $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from
            ->modify('first day of this month')
            ->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to
            ->modify('last day of this month')
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrderStats($from, $to, $filter);
    }

    /**
     * @inheritdoc
     */
    public function calculateYearOrderStats(\DateTime $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from
            ->modify('first day of january ' . $date->format('Y'))
            ->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to
            ->modify('last day of december ' . $date->format('Y'))
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrderStats($from, $to, $filter);
    }

    /**
     * Creates an empty result.
     *
     * @return array
     */
    public function createEmptyResult(): array
    {
        return [
            'revenue'  => '0',
            'shipping' => '0',
            'margin'   => '0',
            'orders'   => '0',
            'items'    => '0',
            'average'  => '0',
            'details'  => array_fill_keys(SaleSources::getSources(), '0'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function calculateOrderStats(\DateTime $from, \DateTime $to, StatFilter $filter = null): array
    {
        $data = $this->createStatQuery($filter)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE)
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

            $query = $this->createDetailQuery($filter);
            foreach (SaleSources::getSources() as $source) {
                $data = $query
                    ->setParameter('from', $from, Types::DATETIME_MUTABLE)
                    ->setParameter('to', $to, Types::DATETIME_MUTABLE)
                    ->setParameter('source', $source)
                    ->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

                if ($data) {
                    $result['details'][$source] = (string)round($data['net'] - $data['shipping'], 3);
                } else {
                    $result['details'][$source] = '0';
                }
            }

            return $result;
        }

        return null;
    }

    /**
     * Returns the stat query.
     *
     * @param StatFilter $filter
     *
     * @return Query
     */
    protected function createStatQuery(StatFilter $filter = null): Query
    {
        $qb = $this->getOrderRepository()->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->select([
                'SUM(o.netTotal) as net',
                'SUM(o.shipmentAmount) as shipping',
                'SUM(o.marginTotal) as margin',
                'COUNT(o.id) as orders',
                'SUM(o.itemsCount) as items',
                'AVG(o.netTotal) as average',
            ])
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('o.state', ':state'))
            ->andWhere($ex->between('o.createdAt', ':from', ':to'))
            ->setParameter('sample', false)
            ->setParameter('state', [
                OrderStates::STATE_COMPLETED,
                OrderStates::STATE_ACCEPTED,
                OrderStates::STATE_PENDING,
            ]);

        $this->filterQueryBuilder($qb, $filter);

        return $qb->getQuery();
    }


    /**
     * Returns the detail query.
     *
     * @param StatFilter|null $filter
     *
     * @return Query
     */
    protected function createDetailQuery(StatFilter $filter = null): Query
    {
        $qb    = $this->getOrderRepository()->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->select([
                'SUM(o.netTotal) as net',
                'SUM(o.shipmentAmount) as shipping',
            ])
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('o.state', ':state'))
            ->andWhere($ex->eq('o.source', ':source'))
            ->andWhere($ex->between('o.createdAt', ':from', ':to'))
            ->setParameter('sample', false)
            ->setParameter('state', [
                OrderStates::STATE_COMPLETED,
                OrderStates::STATE_ACCEPTED,
                OrderStates::STATE_PENDING,
            ]);

        $this->filterQueryBuilder($qb, $filter);

        return $qb->getQuery();
    }

    /**
     * Applies the filter to the query builder.
     *
     * @param QueryBuilder    $qb
     * @param StatFilter|null $filter
     */
    protected function filterQueryBuilder(QueryBuilder $qb, StatFilter $filter = null): void
    {
        if (!$filter) {
            return;
        }

        if (!empty($countries = $filter->getCountries())) {
            $qb
                ->join('o.invoiceAddress', 'a')
                ->join('a.country', 'c');

            if ($filter->isExcludeCountries()) {
                $qb->andWhere($qb->expr()->notIn('c.code', ':countries'));
            } else {
                $qb->andWhere($qb->expr()->in('c.code', ':countries'));
            }

            $qb->setParameter('countries', $countries);
        }
    }

    /**
     * Returns the stock unit repository.
     *
     * @return EntityRepository
     */
    protected function getStockUnitRepository(): EntityRepository
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
    protected function getOrderRepository(): EntityRepository
    {
        if (null !== $this->orderRepository) {
            return $this->orderRepository;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->orderRepository = $this->registry->getRepository($this->orderClass);
    }
}
