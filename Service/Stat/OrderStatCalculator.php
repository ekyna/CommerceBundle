<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stat\Calculator\AbstractStatCalculator;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\StatHelperInterface;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class OrderStatCalculator
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStatCalculator extends AbstractStatCalculator implements StatCalculatorInterface
{
    private ?Query $statQuery = null;

    public function __construct(
        StatHelperInterface                $statHelper,
        protected readonly ManagerRegistry $registry,
        protected readonly string          $orderClass,
    ) {
        parent::__construct($statHelper);
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
            'count'    => '0',
            'average'  => '0',
        ];
    }

    /**
     * @return array{
     *      revenue: string,
     *      shipping: string,
     *      margin: string,
     *      count: string,
     *      average: string
     *  }
     */
    protected function calculateStats(DateRange $range): array
    {
        $query = $this
            ->createStatQuery()
            ->setParameter('from', $range->getStart(), Types::DATETIME_MUTABLE)
            ->setParameter('to', $range->getEnd(), Types::DATETIME_MUTABLE);

        if (null !== $data = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR)) {
            $data = array_map(static fn($val) => new Decimal((string)($val ?? 0)), $data);
        }

        if (empty($data)) {
            return [];
        }

        $margin = new Margin(
            $data['revenue_product'],
            $data['revenue_shipping'],
            $data['cost_product'],
            $data['cost_supply'],
            $data['cost_shipment'],
        );

        return [
            'revenue'  => $margin->getRevenueProduct()->toFixed(3),
            'shipping' => $margin->getRevenueShipment()->toFixed(3),
            'cost'     => $margin->getCostTotal(false)->toFixed(3),
            'count'    => $data['count']->toFixed(),
            'average'  => $data['average']->toFixed(3),
        ];
    }

    /**
     * Returns the stat query.
     */
    protected function createStatQuery(): Query
    {
        if (null !== $this->statQuery) {
            return $this->statQuery;
        }

        $qb = $this
            ->registry
            ->getManagerForClass($this->orderClass)
            ->createQueryBuilder();

        $ex = $qb->expr();

        $qb
            ->select([
                'SUM(o.margin.revenueProduct) as revenue_product',
                'SUM(o.margin.revenueShipment) as revenue_shipping',
                'SUM(o.margin.costProduct) as cost_product',
                'SUM(o.margin.costSupply) as cost_supply',
                'SUM(o.margin.costShipment) as cost_shipment',
                'COUNT(o.id) as count',
                'AVG(o.netTotal) as average',
            ])
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

        return $this->statQuery = $qb->getQuery();
    }
}
