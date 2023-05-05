<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\OrderRepository as BaseRepository;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface as ComponentCustomer;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class OrderRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderRepository extends BaseRepository
{
    // TODO location / weight

    public function findOneByCustomerAndNumber(ComponentCustomer $customer, string $number): ?OrderInterface
    {
        $qb = $this->createQueryBuilder('o');

        $owner = $qb->expr()->orX(
            $qb->expr()->eq('c', ':customer'),
            $qb->expr()->eq('c.parent', ':customer'),
            $qb->expr()->eq('o.originCustomer', ':customer')
        );

        $parameters = [
            'customer' => $customer,
            'number'   => $number,
        ];

        if ($customer instanceof CustomerInterface && $customer->isCanReadParentOrders()) {
            $owner->add($qb->expr()->eq('c', ':parent'));
            $parameters['parent'] = $customer->getParent();
        }

        $sale = $qb
            ->join('o.customer', 'c')
            ->andWhere($owner)
            ->andWhere($qb->expr()->eq('o.number', ':number'))
            ->getQuery()
            ->setParameters($parameters)
            ->getOneOrNullResult();

        if (null !== $sale) {
            $this
                ->loadLines($sale)
                ->loadPayments($sale);
        }

        return $sale;
    }

    /**
     * Finds map locations.
     */
    public function findLocations(array $groups): array
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        $qb
            ->select([
                'SUM(o.netTotal) as net',
                'a.latitude',
                'a.longitude',
                "CONCAT(a.latitude, ',', a.longitude) as latlng",
            ])
            ->andWhere($ex->isNotNull('a.latitude'))
            ->andWhere($ex->isNotNull('a.longitude'))
            ->andWhere($ex->eq('o.sample', ':sample'))
            ->join('o.invoiceAddress', 'a')
            ->addGroupBy('latlng');

        $parameters = [
            'sample' => false,
        ];
        if (!empty($groups)) {
            $qb->andWhere($ex->in('o.customerGroup', ':groups'));
            $parameters['groups'] = $groups;
        }

        $results = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getScalarResult();

        $min = INF; $max = 0;
        foreach ($results as $r) {
            if ($r['net'] < $min) {
                $min = $r['net'];
            }
            if ($r['net'] > $max) {
                $max = $r['net'];
            }
        }

        return array_map(function ($r) use ($min, $max) {
            return [
                'lat'    => $r['latitude'],
                'lng'    => $r['longitude'],
                'weight' => round(($r['net'] - $min) * 9 / $max + 1, 2),
            ];
        }, $results);
    }
}
