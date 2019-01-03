<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CheckoutEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutEvent extends Event
{
    const EVENT_INIT          = 'ekyna_commerce.checkout.init';
    const EVENT_SHIPMENT_STEP = 'ekyna_commerce.checkout.shipment_step';
    const EVENT_PAYMENT_STEP  = 'ekyna_commerce.checkout.payment_step';
    const EVENT_CONFIRMATION  = 'ekyna_commerce.checkout.confirmation';


    /**
     * @var SaleInterface
     */
    private $sale;


    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     */
    public function __construct(SaleInterface $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Returns the sale.
     *
     * @return SaleInterface
     */
    public function getSale()
    {
        return $this->sale;
    }
}
