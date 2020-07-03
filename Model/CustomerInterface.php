<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface as BaseInterface;

/**
 * Interface CustomerInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends BaseInterface, InChargeSubjectInterface, TagsSubjectInterface
{
    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser(): ?UserInterface;

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return $this|CustomerInterface
     */
    public function setUser(UserInterface $user = null): CustomerInterface;
}
