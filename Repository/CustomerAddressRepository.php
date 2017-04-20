<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CustomerAddressRepository as BaseRepository;

/**
 * Class CustomerAddressRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressRepository extends BaseRepository
{
    /**
     * Finds map locations.
     */
    public function findLocations(array $groups, bool $invoice): array
    {
        $qb = $this->createQueryBuilder('a');
        $ex = $qb->expr();

        $qb
            ->select([
                'c.id',
                'c.company',
                'a.latitude',
                'a.longitude',
                "CONCAT(a.latitude, ',', a.longitude) as latlng",
            ])
            ->join('a.customer', 'c')
            ->andWhere($ex->isNotNull('a.latitude'))
            ->andWhere($ex->isNotNull('a.longitude'))
            ->andWhere($ex->eq($invoice ? 'a.invoiceDefault' : 'a.deliveryDefault', ':type'))
            ->addGroupBy('latlng');

        $parameters = [
            'type' => true,
        ];

        if (!empty($groups)) {
            $qb
                ->join('c.customerGroup', 'g')
                ->andWhere($ex->in('c.customerGroup', ':groups'));

            $parameters['groups'] = $groups;
        }

        $results = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getScalarResult();

        return array_map(function ($r) {
            return [
                'title'      => $r['company'],
                'position'   => [
                    'lat' => $r['latitude'],
                    'lng' => $r['longitude'],
                ],
                'customerId' => $r['id'],
            ];
        }, $results);
    }
}
