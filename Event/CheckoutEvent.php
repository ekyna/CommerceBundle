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
    const EVENT_CONTENT       = 'ekyna_commerce.checkout.content';


    /**
     * @var SaleInterface
     */
    private $sale;

    /**
     * @var string
     */
    private $content;


    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     */
    public function __construct(SaleInterface $sale = null)
    {
        $this->sale = $sale;
    }

    /**
     * Returns the sale.
     *
     * @return SaleInterface|null
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Returns the content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the content.
     *
     * @param string $content
     *
     * @return CheckoutEvent
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }
}
