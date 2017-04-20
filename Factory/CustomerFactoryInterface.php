<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Customer\Factory\CustomerFactoryInterface as BaseFactory;

/**
 * Interface CustomerFactoryInterface
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method CustomerInterface create()
 */
interface CustomerFactoryInterface extends BaseFactory
{
    public function createWithUser(UserInterface $user): CustomerInterface;
}
