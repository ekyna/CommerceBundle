<?php

declare(strict_types=1);

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
    public function getUser(): ?UserInterface;

    public function setUser(?UserInterface $user): CustomerInterface;
}
