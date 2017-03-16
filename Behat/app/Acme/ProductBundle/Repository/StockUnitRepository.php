<?php

namespace Acme\ProductBundle\Repository;

use Acme\ProductBundle\Entity\Product;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class StockUnitRepository
 * @package Acme\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findAvailableOrPendingBySubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq('su.product', ':product'))
            ->andWhere($qb->expr()->in('su.state', ':state'))
            ->setParameter('product', $subject)
            ->setParameter('state', [StockUnitStates::STATE_OPENED, StockUnitStates::STATE_PENDING])
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findNewBySubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq('su.product', ':product'))
            ->andWhere($qb->expr()->eq('su.state', ':state'))
            ->setParameter('product', $subject)
            ->setParameter('state', StockUnitStates::STATE_NEW)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findNotClosedSubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->neq($alias . '.state', ':state'))
            ->setParameter('product', $subject)
            ->setParameter('state', StockUnitStates::STATE_CLOSED)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findAssignableBySubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof Product) {
            throw new \InvalidArgumentException('Expected instance of ' . Product::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull($alias . '.supplierOrderItem'), // Not yet linked to a supplier order
                $qb->expr()->lt($alias . '.reservedQuantity', $alias . '.orderedQuantity'),   // Reserved lower than ordered
                $qb->expr()->eq('SIZE(' . $alias . '.stockAssignments)', 0) // No assignments TODO remove ?
            ))
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'su';
    }
}
