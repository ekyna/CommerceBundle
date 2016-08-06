<?php


namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\UserBundle\Model\IdentityInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface as BaseInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends BaseInterface, IdentityInterface
{

}
