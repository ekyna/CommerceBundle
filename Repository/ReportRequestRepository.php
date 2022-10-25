<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Entity\ReportRequest;

/**
 * Class ReportRequestRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportRequest::class);
    }

    public function findOneByUser(UserInterface $user): ?ReportRequest
    {
        return $this->findOneBy(['user' => $user]);
    }
}
