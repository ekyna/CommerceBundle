<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\UiBundle\Model\UiButton;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SaleButtonsEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleButtonsEvent extends Event
{
    public const SALE_BUTTONS = 'ekyna_commerce.sale.buttons';

    private SaleInterface $sale;
    /** @var UiButton[] */
    private array $buttons = [];


    public function __construct(SaleInterface $sale)
    {
        $this->sale = $sale;
    }

    public function getSale(): SaleInterface
    {
        return $this->sale;
    }

    /**
     * @return UiButton[]
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function addButton(UiButton $button): SaleButtonsEvent
    {
        $this->buttons[] = $button;

        return $this;
    }
}
