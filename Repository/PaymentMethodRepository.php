<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\PaymentMethodRepository as BaseRepository;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;

/**
 * Class PaymentMethodRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodRepository extends BaseRepository
{
    /**
     * @var Query
     */
    private $defaultFactoryQuery;

    /**
     * @var Query
     */
    private $availableFactoryQuery;


    /**
     * @inheritDoc
     */
    public function findByFactoryName(string $name, bool $available = true): array
    {
        return $this
            ->getFactoryNameQuery($available)
            ->setParameter('name', $name)
            ->setMaxResults(null)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByFactoryName(string $name, bool $available = true): ?PaymentMethodInterface
    {
        return $this
            ->getFactoryNameQuery($available)
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find by factory name" query.
     *
     * @param bool   $available
     *
     * @return Query
     */
    private function getFactoryNameQuery(bool $available = true): Query
    {
        if ($available) {
            if ($this->availableFactoryQuery) {
                return $this->availableFactoryQuery;
            }

            $qb = $this->createFactoryNameQueryBuilder();

            return $this->availableFactoryQuery = $qb
                ->andWhere($qb->expr()->eq('m.available', ':available'))
                ->setParameter('available', true)
                ->getQuery();
        }

        if ($this->defaultFactoryQuery) {
            return $this->defaultFactoryQuery;
        }

        $qb = $this->createFactoryNameQueryBuilder();

        return $this->defaultFactoryQuery = $qb->getQuery();
    }

    /**
     * Creates a "find by factory name" query builder.
     *
     * @return QueryBuilder
     */
    private function createFactoryNameQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->andWhere($qb->expr()->eq('m.factoryName', ':name'))
            ->andWhere($qb->expr()->eq('m.enabled', ':enabled'))
            ->setParameter('enabled', true);
    }
}
