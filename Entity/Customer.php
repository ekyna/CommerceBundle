<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Customer\Entity\Customer as BaseCustomer;
use Ekyna\Bundle\CmsBundle\Model as Cms;

/**
 * Class Customer
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer extends BaseCustomer implements Model\CustomerInterface
{
    use Cms\TagsSubjectTrait;
    use Model\InChargeSubjectTrait;

    protected ?UserInterface $user = null;

    public function __construct()
    {
        parent::__construct();

        $this->initializeTags();
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): Model\CustomerInterface
    {
        $this->user = $user;

        return $this;
    }
}
