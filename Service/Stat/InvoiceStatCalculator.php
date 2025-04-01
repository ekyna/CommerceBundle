<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stat;

use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Stat\Calculator\AbstractStatCalculator;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\StatHelperInterface;
use Ekyna\Component\Resource\Model\DateRange;

use function array_map;

/**
 * Class InvoiceStatCalculator
 * @package Ekyna\Bundle\CommerceBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceStatCalculator extends AbstractStatCalculator implements StatCalculatorInterface
{
    private ?Query $query = null;

    public function __construct(
        StatHelperInterface                $statHelper,
        protected readonly ManagerRegistry $registry,
        protected readonly string          $invoiceClass,
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
        ];
    }

    /**
     * @return array{
     *      revenue: string,
     *      shipping: string,
     *      margin: string,
     *      count: string,
     *  }
     */
    protected function calculateStats(DateRange $range): array
    {
        $revenue = new Decimal(0);
        $shipping = new Decimal(0);
        $cost = new Decimal(0);
        $count = new Decimal(0);

        // Invoices
        $query = $this
            ->createStatQuery()
            ->setParameter('credit', false)
            ->setParameter('from', $range->getStart(), Types::DATETIME_MUTABLE)
            ->setParameter('to', $range->getEnd(), Types::DATETIME_MUTABLE);

        if (null !== $data = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR)) {
            $data = array_map(static fn($val) => new Decimal((string)($val ?? 0)), $data);

            $margin = new Margin(
                $data['revenue_product'],
                $data['revenue_shipping'],
                $data['cost_product'],
                $data['cost_supply'],
                $data['cost_shipment'],
            );

            $revenue = $revenue->add($margin->getRevenueProduct());
            $shipping = $shipping->add($margin->getRevenueShipment());
            $cost = $cost->add($margin->getCostTotal(false));
            $count = $count->add($data['count']);
        }

        // Credits
        $query = $this
            ->createStatQuery()
            ->setParameter('credit', true)
            ->setParameter('from', $range->getStart(), Types::DATETIME_MUTABLE)
            ->setParameter('to', $range->getEnd(), Types::DATETIME_MUTABLE);

        if (null !== $data = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR)) {
            $data = array_map(static fn($val) => new Decimal((string)($val ?? 0)), $data);

            $margin = new Margin(
                $data['revenue_product'],
                $data['revenue_shipping'],
                $data['cost_product'],
                $data['cost_supply'],
                $data['cost_shipment'],
            );

            $revenue = $revenue->sub($margin->getRevenueProduct());
            $shipping = $shipping->sub($margin->getRevenueShipment());
            $cost = $cost->sub($margin->getCostTotal(false));
            $count = $count->sub($data['count']);
        }

        return [
            'revenue'  => $revenue->toFixed(3),
            'shipping' => $shipping->toFixed(3),
            'cost'     => $cost->toFixed(3),
            'count'    => $count->toFixed(3),
        ];
    }

    /**
     * Returns the stat query.
     */
    protected function createStatQuery(): Query
    {
        if (null !== $this->query) {
            return $this->query;
        }

        $qb = $this
            ->registry
            ->getManagerForClass($this->invoiceClass)
            ->createQueryBuilder();

        $ex = $qb->expr();

        $qb
            ->select([
                'SUM(i.margin.revenueProduct) as revenue_product',
                'SUM(i.margin.revenueShipment) as revenue_shipping',
                'SUM(i.margin.costProduct) as cost_product',
                'SUM(i.margin.costSupply) as cost_supply',
                'SUM(i.margin.costShipment) as cost_shipment',
                'COUNT(i.id) as count',
            ])
            ->from($this->invoiceClass, 'i')
            ->andWhere($ex->eq('i.credit', ':credit'))
            ->andWhere($ex->between('i.createdAt', ':from', ':to'));

        return $this->query = $qb->getQuery();
    }
}
