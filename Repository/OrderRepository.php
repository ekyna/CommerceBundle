<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\OrderRepository as BaseRepository;

/**
 * Class OrderRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderRepository extends BaseRepository
{
    // TODO location / weight

    /**
     * Finds map locations.
     *
     * @param array $groups
     *
     * @return array
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
                'lat'    => floatval($r['latitude']),
                'lng'    => floatval($r['longitude']),
                'weight' => round(($r['net'] - $min) * 9 / $max + 1, 2),
            ];
        }, $results);
    }
}
