<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Entity\Order as BaseOrder;

/**
 * Class Order
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order extends BaseOrder implements OrderInterface
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
