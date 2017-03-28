<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository
{
    /**
     * Finds the customer by user.
     *
     * @param UserInterface $user
     *
     * @return null|CustomerInterface
     */
    public function findOneByUser(UserInterface $user)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findOneBy(['user' => $user]);
    }
}
