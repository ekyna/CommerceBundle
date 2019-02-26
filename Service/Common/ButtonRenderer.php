<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Event\SaleButtonsEvent;
use Ekyna\Bundle\CoreBundle\Model\UiButton;
use Ekyna\Bundle\CoreBundle\Service\Ui\UiRenderer;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ButtonRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ButtonRenderer
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var UiRenderer
     */
    private $uiRenderer;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param UiRenderer               $uiRenderer
     */
    public function __construct(EventDispatcherInterface $dispatcher, UiRenderer $uiRenderer)
    {
        $this->dispatcher = $dispatcher;
        $this->uiRenderer = $uiRenderer;
    }

    /**
     * Renders the sale custom buttons.
     *
     * @param SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleCustomButtons(SaleInterface $sale)
    {
        $event = new SaleButtonsEvent($sale);

        $this->dispatcher->dispatch(SaleButtonsEvent::SALE_BUTTONS, $event);

        if (empty($buttons = $event->getButtons())) {
            return '';
        }

        usort($buttons, function (UiButton $a, UiButton $b) {
            return $a->getPriority() - $b->getPriority();
        });

        $output = '';

        foreach ($buttons as $button) {
            $output .= $this->uiRenderer->renderButton($button);
        }

        return $output;
    }
}
