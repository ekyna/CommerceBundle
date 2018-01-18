<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Component\Commerce\Order\Model\OrderInterface as BaseInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends BaseInterface, InChargeSubjectInterface, TaggedSaleInterface
{

}
