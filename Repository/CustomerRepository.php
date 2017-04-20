<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CustomerRepository as BaseRepository;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends BaseRepository
{
    /**
     * Finds the customer by user.
     *
     * @param UserInterface $user
     *
     * @return CustomerInterface|null
     */
    public function findOneByUser(UserInterface $user): ?CustomerInterface
    {
        return $this->findOneBy(['user' => $user]);
    }
}
