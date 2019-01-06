<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class TicketMessageRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageRepository extends ResourceRepository
{
    /**
     * Finds admin messages to notify to customers.
     *
     * @return TicketMessageInterface[]
     */
    public function findNotNotifiedForCustomers()
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->isNull('m.notifiedAt'))
            ->andWhere($ex->isNotNull('m.admin'))
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * Finds customers messages to notify to the given admin user.
     *
     * @param UserInterface $inCharge
     *
     * @return TicketMessageInterface[]
     */
    public function findNotNotifiedByInCharge(UserInterface $inCharge)
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        return $qb
            ->join('m.ticket', 't')
            ->andWhere($ex->isNull('m.notifiedAt'))
            ->andWhere($ex->isNull('m.admin'))
            ->andWhere($ex->eq('t.inCharge', ':in_charge'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('in_charge', $inCharge)
            ->getResult();
    }

    /**
     * Finds customers messages to notify to default admin.
     *
     * @return TicketMessageInterface[]
     */
    public function findNotNotifiedAndUnassigned()
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        return $qb
            ->join('m.ticket', 't')
            ->andWhere($ex->isNull('m.notifiedAt'))
            ->andWhere($ex->isNull('m.admin'))
            ->andWhere($ex->isNull('t.inCharge'))
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }
}
