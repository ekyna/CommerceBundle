<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CheckoutEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutEvent extends Event
{
    public const EVENT_INIT          = 'ekyna_commerce.checkout.init';
    public const EVENT_SHIPMENT_STEP = 'ekyna_commerce.checkout.shipment_step';
    public const EVENT_PAYMENT_STEP  = 'ekyna_commerce.checkout.payment_step';
    public const EVENT_CONFIRMATION  = 'ekyna_commerce.checkout.confirmation';
    public const EVENT_CONTENT       = 'ekyna_commerce.checkout.content';


    private ?SaleInterface $sale;
    private ?string $content = null;


    public function __construct(?SaleInterface $sale)
    {
        $this->sale = $sale;
    }

    public function getSale(): ?SaleInterface
    {
        return $this->sale;
    }

    public function setContent(?string $content): CheckoutEvent
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
