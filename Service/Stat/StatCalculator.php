<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Resource\Doctrine\ORM\Hydrator\IdHydrator;
use LogicException;

/**
 * Class StatCalculator
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCalculator implements StatCalculatorInterface
{
    protected ManagerRegistry         $registry;
    protected AmountCalculatorFactory $amountCalculatorFactory;
    protected MarginCalculatorFactory $marginCalculatorFactory;
    protected string                  $orderClass;
    protected string                  $defaultCurrency;
    protected bool                    $skipMode = false;

    public function __construct(
        ManagerRegistry         $registry,
        AmountCalculatorFactory $amountCalculatorFactory,
        MarginCalculatorFactory $marginCalculatorFactory,
        string                  $orderClass,
        string                  $defaultCurrency
    ) {
        $this->registry = $registry;
        $this->amountCalculatorFactory = $amountCalculatorFactory;
        $this->marginCalculatorFactory = $marginCalculatorFactory;
        $this->orderClass = $orderClass;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function setSkipMode(bool $skip): void
    {
        $this->skipMode = $skip;
    }

    public function calculateStockStats(): array
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

    public function calculateDayOrderStats(DateTimeInterface $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from->setTime(0, 0);

        $to = clone $date;
        $to->setTime(23, 59, 59, 999999);

        return $this->calculateOrdersStats($from, $to, $filter);
    }

    public function calculateMonthOrderStats(DateTimeInterface $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from
            ->modify('first day of this month')
            ->setTime(0, 0);

        $to = clone $date;
        $to
            ->modify('last day of this month')
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrdersStats($from, $to, $filter);
    }

    public function calculateYearOrderStats(DateTimeInterface $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from
            ->modify('first day of january ' . $date->format('Y'))
            ->setTime(0, 0);

        $to = clone $date;
        $to
            ->modify('last day of december ' . $date->format('Y'))
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrdersStats($from, $to, $filter);
    }

    /**
     * Creates an empty result.
     */
    public function createEmptyResult(): array
    {
        return [
            'revenue'  => '0',
            'shipping' => '0',
            'cost'     => '0',
            'orders'   => '0',
            'items'    => '0',
            'average'  => '0',
            'details'  => array_fill_keys(SaleSources::getSources(), '0'),
        ];
    }

    /**
     * Calculates the order stats.
     */
    protected function calculateOrdersStats(
        DateTimeInterface $from,
        DateTimeInterface $to,
        StatFilter        $filter = null
    ): array {
        $query = $this
            ->createStatQuery($filter)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE);

        if ($filter && !empty($filter->getSubjects())) {
            $data = $this->calculateOrders($query->getResult(IdHydrator::NAME), $filter);
        } elseif (null !== $data = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR)) {
            $data = array_map(static fn($val) => new Decimal((string)($val ?? 0)), $data);
        }

        if ($data) {
            /**
             * @var array{
             *     revenue: Decimal,
             *     shipping: Decimal,
             *     margin: Decimal,
             *     orders: Decimal,
             *     items: Decimal,
             *     average: Decimal
             * } $result
             */
            $margin = new Margin(
                $data['revenue_product'],
                $data['revenue_shipping'],
                $data['cost_product'],
                $data['cost_supply'],
                $data['cost_shipment'],
            );

            $result = [
                'revenue'  => $margin->getRevenueProduct()->toFixed(3),
                'shipping' => $margin->getRevenueShipment()->toFixed(3),
                'cost'     => $margin->getCostTotal(false)->toFixed(3),
                'orders'   => $data['orders']->toFixed(),
                'items'    => $data['items']->toFixed(),
                'average'  => $data['average']->toFixed(3),
                'details'  => [],
            ];

            if (!($filter && !empty($filter->getSubjects()))) {
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
            }

            return $result;
        }

        return [];
    }

    /**
     * Calculates the order stats.
     *
     * @param int[] $orders
     *
     * @return array{revenue: float, shipping: float, margin: float, orders: int, items: int, average: float}
     */
    protected function calculateOrders(array $orders, StatFilter $filter): array
    {
        $manager = $this->registry->getManagerForClass($this->orderClass);
        /** @var OrderRepositoryInterface $repository */
        $repository = $manager->getRepository($this->orderClass);

        $data = [
            'revenue_product'  => new Decimal(0),
            'revenue_shipping' => new Decimal(0),
            'cost_product'     => new Decimal(0),
            'cost_supply'      => new Decimal(0),
            'cost_shipment'    => new Decimal(0),
            'orders'           => new Decimal(0),
            'items'            => new Decimal(0),
            'average'          => new Decimal(0),
        ];

        if (!$this->skipMode) {
            $marginCalculator = $this->marginCalculatorFactory->create(filter: $filter);
        } else {
            $marginCalculator = $this->marginCalculatorFactory->create();
        }

        foreach ($orders as $id) {
            if (!$order = $repository->find($id)) {
                throw new LogicException("Order #$id not found.");
            }

            if ($this->skipMode && $this->hasSkippedItem($order->getItems()->toArray(), $filter)) {
                $manager->clear();
                continue;
            }

            $margin = $marginCalculator->calculateSale($order);

            // Ignore order if not skip mode and gross amount equals to zero
            if (!$this->skipMode && $margin->getRevenueProduct()->isZero()) {
                $manager->clear();
                continue;
            }

            $data['revenue_product'] += $margin->getRevenueProduct();
            $data['revenue_shipping'] += $margin->getRevenueShipment();
            $data['cost_product'] += $margin->getCostProduct();
            $data['cost_supply'] += $margin->getCostSupply();
            $data['cost_shipment'] += $margin->getCostShipment();

            $data['orders'] += 1;
            $data['items'] += $order->getItemsCount();
            $data['average'] += $order->getNetTotal();

            $manager->clear(); // TODO Dangerous
        }

        if (0 < $data['orders']) {
            $data['average'] /= $data['orders'];
        }

        return $data;
    }

    /**
     * @param array<OrderItemInterface> $items
     */
    protected function hasSkippedItem(array $items, StatFilter $filter): bool
    {
        foreach ($items as $item) {
            if (!$item->hasSubjectIdentity()) {
                if (!$filter->isExcludeSubjects()) {
                    return true;
                }

                continue;
            }

            if (!$filter->isExcludeSubjects() xor $filter->hasSubject($item->getSubjectIdentity())) {
                return true;
            }

            if ($this->hasSkippedItem($item->getChildren()->toArray(), $filter)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the stat query.
     */
    protected function createStatQuery(StatFilter $filter = null): Query
    {
        $qb = $this
            ->registry
            ->getManagerForClass($this->orderClass)
            ->createQueryBuilder();

        $ex = $qb->expr();

        if ($filter && !empty($filter->getSubjects())) {
            $qb->select('o.id');
        } else {
            $qb->select([
                'SUM(o.margin.revenueProduct) as revenue_product',
                'SUM(o.margin.revenueShipment) as revenue_shipping',
                'SUM(o.margin.costProduct) as cost_product',
                'SUM(o.margin.costSupply) as cost_supply',
                'SUM(o.margin.costShipment) as cost_shipment',
                'COUNT(o.id) as orders',
                'SUM(o.itemsCount) as items',
                'AVG(o.netTotal) as average',
            ]);
        }

        $qb
            ->from($this->orderClass, 'o')
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('o.state', ':state'))
            ->andWhere($ex->between('o.acceptedAt', ':from', ':to'))
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
     */
    protected function createDetailQuery(StatFilter $filter = null): Query
    {
        $qb = $this
            ->registry
            ->getManagerForClass($this->orderClass)
            ->createQueryBuilder();

        $ex = $qb->expr();

        $qb
            ->from($this->orderClass, 'o')
            ->select([
                'SUM(o.netTotal) as net',
                'SUM(o.shipmentAmount) as shipping',
            ])
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->andWhere($ex->in('o.state', ':state'))
            ->andWhere($ex->eq('o.source', ':source'))
            ->andWhere($ex->between('o.acceptedAt', ':from', ':to'))
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
     */
    protected function filterQueryBuilder(QueryBuilder $qb, StatFilter $filter = null): void
    {
        if (!$filter) {
            return;
        }

        if (empty($countries = $filter->getCountries())) {
            return;
        }

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
