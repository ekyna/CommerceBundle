<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\UserBundle\Model\IdentityInterface;
use Ekyna\Component\Commerce\Order\Entity\OrderAddress as BaseAddress;

/**
 * Class OrderAddress
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddress extends BaseAddress implements IdentityInterface
{
    /**
     * @var string
     */
    protected $gender;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string
     */
    protected $mobile;


    /**
     * @inheritdoc
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @inheritdoc
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Returns the phone.
     *
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets the phone.
     *
     * @param mixed $phone
     *
     * @return OrderAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Returns the mobile.
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Sets the mobile.
     *
     * @param string $mobile
     *
     * @return OrderAddress
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }
}
