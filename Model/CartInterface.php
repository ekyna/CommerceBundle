<?php


namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\UserBundle\Model\IdentityInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface as BaseInterface;

/**
 * Interface CartInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartInterface extends BaseInterface, IdentityInterface
{

}
