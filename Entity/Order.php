<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Commerce\Order\Entity\Order as BaseOrder;

/**
 * Class Order
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Model\CustomerInterface $customer
 */
class Order extends BaseOrder implements Model\OrderInterface
{
    use Model\InChargeSubjectTrait;
    use Model\TaggedSaleTrait;


    public function __construct()
    {
        parent::__construct();

        $this->initializeTags();
    }
}
