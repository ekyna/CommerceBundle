<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Support\Repository\TicketMessageRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class TicketMessageRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageRepository extends ResourceRepository implements TicketMessageRepositoryInterface
{
    /**
     * Finds admin messages to notify to customers.
     *
     * @return array<TicketMessageInterface>
     */
    public function findNotNotifiedForCustomers(): array
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        return $qb
            ->join('m.ticket', 't')
            ->join('t.customer', 'c')
            ->andWhere($ex->isNull('m.notifiedAt'))
            ->andWhere($ex->isNotNull('c.user'))
            ->andWhere($ex->isNotNull('m.admin'))
            ->andWhere($ex->eq('m.internal', ':internal'))
            ->andWhere($ex->eq('t.internal', ':internal'))
            ->andWhere($ex->eq('m.notify', ':notify'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('notify', true)
            ->setParameter('internal', false)
            ->getResult();
    }

    /**
     * Finds customers messages to notify to the given admin user.
     *
     * @return array<TicketMessageInterface>
     */
    public function findNotNotifiedByInCharge(UserInterface $inCharge): array
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        return $qb
            ->join('m.ticket', 't')
            ->andWhere($ex->isNull('m.notifiedAt'))
            ->andWhere($ex->isNull('m.admin'))
            ->andWhere($ex->eq('m.notify', ':notify'))
            ->andWhere($ex->eq('t.inCharge', ':in_charge'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'in_charge' => $inCharge,
                'notify'    => true,
            ])
            ->getResult();
    }

    /**
     * Finds customers messages to notify to default admin.
     *
     * @return array<TicketMessageInterface>
     */
    public function findNotNotifiedAndUnassigned(): array
    {
        $qb = $this->createQueryBuilder('m');
        $ex = $qb->expr();

        return $qb
            ->join('m.ticket', 't')
            ->andWhere($ex->isNull('m.notifiedAt'))
            ->andWhere($ex->isNull('m.admin'))
            ->andWhere($ex->eq('m.notify', ':notify'))
            ->andWhere($ex->isNull('t.inCharge'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('notify', true)
            ->getResult();
    }
}
