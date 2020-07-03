<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
    use Model\InChargeSubjectTrait,
        Cms\TagsSubjectTrait;

    /**
     * @var UserInterface
     */
    protected $user;


    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->tags = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function setUser(UserInterface $user = null): Model\CustomerInterface
    {
        $this->user = $user;

        return $this;
    }
}
