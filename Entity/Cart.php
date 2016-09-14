<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Entity\Cart as BaseCart;

/**
 * Class Cart
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Cart extends BaseCart implements CartInterface
{
    /**
     * @var string
     */
    private $gender;


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
}
