<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class SaleEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleEvent extends ResourceEvent
{
    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     */
    public function __construct(SaleInterface $sale)
    {
        $this->setResource($sale);
    }
}
