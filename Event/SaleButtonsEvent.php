<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CoreBundle\Model\UiButton;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SaleButtonsEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleButtonsEvent extends Event
{
    const SALE_BUTTONS = 'ekyna_commerce.sale.buttons';


    /**
     * @var SaleInterface
     */
    private $sale;

    /**
     * @var UiButton[]
     */
    private $buttons;

    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     */
    public function __construct(SaleInterface $sale)
    {
        $this->sale = $sale;
        $this->buttons = [];
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

    /**
     * Returns the buttons.
     *
     * @return UiButton[]
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * Adds the button.
     *
     * @param UiButton $button
     *
     * @return SaleButtonsEvent
     */
    public function addButton(UiButton $button)
    {
        $this->buttons[] = $button;

        return $this;
    }
}
