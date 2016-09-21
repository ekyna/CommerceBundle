<?php


namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\UserBundle\Model\IdentityInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface as BaseInterface;

/**
 * Interface QuoteInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteInterface extends BaseInterface, IdentityInterface
{

}
