<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
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
     * @var AmountCalculatorFactory
     */
    protected $amountCalculatorFactory;

    /**
     * @var MarginCalculatorFactory
     */
    protected $marginCalculatorFactory;

    /**
     * @var string
     */
    protected $orderClass;

    /**
     * @var string
     */
    protected $defaultCurrency;

    /**
     * @var bool
     */
    protected $skipMode = false;


    /**
     * Constructor.
     *
     * @param RegistryInterface       $registry
     * @param AmountCalculatorFactory $amountCalculatorFactory
     * @param MarginCalculatorFactory $marginCalculatorFactory
     * @param string                  $orderClass
     * @param string                  $defaultCurrency
     */
    public function __construct(
        RegistryInterface $registry,
        AmountCalculatorFactory $amountCalculatorFactory,
        MarginCalculatorFactory $marginCalculatorFactory,
        string $orderClass,
        string $defaultCurrency
    ) {
        $this->registry = $registry;
        $this->amountCalculatorFactory = $amountCalculatorFactory;
        $this->marginCalculatorFactory = $marginCalculatorFactory;
        $this->orderClass = $orderClass;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritdoc
     */
    public function setSkipMode(bool $skip): void
    {
        $this->skipMode = $skip;
    }

    /**
     * @inheritdoc
     */
    public function calculateStockStats(): array
    {
        $qb = $this
            ->registry
            ->getEntityManagerForClass(AbstractStockUnit::class)
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

    /**
     * @inheritdoc
     */
    public function calculateDayOrderStats(DateTime $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to->setTime(23, 59, 59, 999999);

        return $this->calculateOrdersStats($from, $to, $filter);
    }

    /**
     * @inheritdoc
     */
    public function calculateMonthOrderStats(DateTime $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from
            ->modify('first day of this month')
            ->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to
            ->modify('last day of this month')
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrdersStats($from, $to, $filter);
    }

    /**
     * @inheritdoc
     */
    public function calculateYearOrderStats(DateTime $date, StatFilter $filter = null): array
    {
        $from = clone $date;
        $from
            ->modify('first day of january ' . $date->format('Y'))
            ->setTime(0, 0, 0, 0);

        $to = clone $date;
        $to
            ->modify('last day of december ' . $date->format('Y'))
            ->setTime(23, 59, 59, 999999);

        return $this->calculateOrdersStats($from, $to, $filter);
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
     * Calculates the order stats.
     *
     * @param DateTime        $from
     * @param DateTime        $to
     * @param StatFilter|null $filter
     *
     * @return array
     */
    protected function calculateOrdersStats(DateTime $from, DateTime $to, StatFilter $filter = null): array
    {
        $query = $this
            ->createStatQuery($filter)
            ->setParameter('from', $from, Types::DATETIME_MUTABLE)
            ->setParameter('to', $to, Types::DATETIME_MUTABLE);

        if ($filter && !empty($filter->getSubjects())) {
            $orders = $query->getResult(AbstractQuery::HYDRATE_SCALAR);
            $data = $this->calculateOrders(array_column($orders, 'id'), $filter);
        } else {
            $data = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
        }

        if ($data) {
            $result = [
                'revenue'  => (string)round($data['revenue'] - $data['shipping'], 3),
                'shipping' => (string)round($data['shipping'], 3),
                'margin'   => (string)round($data['margin'], 3),
                'orders'   => (string)$data['orders'],
                'items'    => (string)$data['items'],
                'average'  => (string)round($data['average'], 3),
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
     * @param int[]      $orders
     * @param StatFilter $filter
     *
     * @return array
     */
    protected function calculateOrders(array $orders, StatFilter $filter): array
    {
        $manager = $this->registry->getEntityManagerForClass($this->orderClass);
        /** @var OrderRepositoryInterface $repository */
        $repository = $manager->getRepository($this->orderClass);

        $data = [
            'revenue'  => 0,
            'shipping' => 0,
            'margin'   => 0,
            'orders'   => 0,
            'items'    => 0,
            'average'  => 0,
        ];

        if (!$this->skipMode) {
            $amountCalculator = $this->amountCalculatorFactory->create($this->defaultCurrency, true, $filter);
            $marginCalculator = $this->marginCalculatorFactory->create($this->defaultCurrency, true, $filter);
        } else {
            $amountCalculator = $this->amountCalculatorFactory->create($this->defaultCurrency, true);
            $marginCalculator = $this->marginCalculatorFactory->create($this->defaultCurrency, true);
        }

        foreach ($orders as $id) {
            if (!$order = $repository->findOneById($id)) {
                throw new \LogicException("Order #$id not found.");
            }

            if ($this->skipMode && $this->hasSkippedItem($order->getItems()->toArray(), $filter)) {
                $manager->clear();
                continue;
            }

            $amountCalculator->clear();

            // TODO calculate revenue based on sold quantities (assignments)
            $result = $amountCalculator->calculateSale($order, true);

            // Ignore order if not skip mode and gross amount equals to zero
            if (!$this->skipMode && 0 === Money::compare($result->getGross(), 0, $this->defaultCurrency)) {
                $manager->clear();
                continue;
            }

            $data['revenue'] += $result->getBase();
            $data['shipping'] += $amountCalculator->calculateSaleShipment($order)->getGross();

            if ($margin = $marginCalculator->calculateSale($order)) {
                $data['margin'] += $amount = $margin->getAmount();
            }

            $data['orders'] += 1;

            $manager->clear(); // TODO Dangerous
        }

        return $data;
    }

    /**
     * @param OrderItemInterface[] $items
     * @param StatFilter           $filter
     *
     * @return bool
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
     *
     * @param StatFilter $filter
     *
     * @return Query
     */
    protected function createStatQuery(StatFilter $filter = null): Query
    {
        $qb = $this
            ->registry
            ->getEntityManagerForClass($this->orderClass)
            ->createQueryBuilder();

        $ex = $qb->expr();

        if ($filter && !empty($filter->getSubjects())) {
            $qb->select('o.id');
        } else {
            $qb->select([
                'SUM(o.revenueTotal) as revenue',
                'SUM(o.shipmentAmount) as shipping',
                'SUM(o.marginTotal) as margin',
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
     *
     * @param StatFilter|null $filter
     *
     * @return Query
     */
    protected function createDetailQuery(StatFilter $filter = null): Query
    {
        $qb = $this
            ->registry
            ->getEntityManagerForClass($this->orderClass)
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
}
