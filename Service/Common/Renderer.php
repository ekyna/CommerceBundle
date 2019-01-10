<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Event\SaleButtonsEvent;
use Ekyna\Bundle\CoreBundle\Model\UiButton;
use Ekyna\Bundle\CoreBundle\Service\Ui\UiRenderer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class Renderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Renderer
{
    /**
     * @var EngineInterface
     */
    private $templating;

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
     * @param EngineInterface $templating
     * @param EventDispatcherInterface $dispatcher
     * @param UiRenderer $uiRenderer
     */
    public function __construct(
        EngineInterface $templating,
        EventDispatcherInterface $dispatcher,
        UiRenderer $uiRenderer
    ) {
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->uiRenderer = $uiRenderer;
    }

    /**
     * Renders the address.
     *
     * @param AddressInterface $address
     * @param bool             $displayPhones
     *
     * @return string
     */
    public function renderAddress(AddressInterface $address, $displayPhones = true)
    {
        return $this->templating->render('@EkynaCommerce/Show/address.html.twig', [
            'address'        => $address,
            'display_phones' => $displayPhones,
        ]);
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

        usort($buttons, function(UiButton $a, UiButton $b) {
            return $a->getPriority() - $b->getPriority();
        });

        $output = '';

        foreach ($buttons as $button) {
            $output .= $this->uiRenderer->renderButton($button);
        }

        return $output;
    }

    /**
     * Renders the sale flags.
     *
     * @param SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleFlags(SaleInterface $sale)
    {
        $flags = '';

        if (SaleSources::SOURCE_WEBSITE === $sale->getSource()) {
            $flags .= '<i class="fa fa-sitemap"></i>';
        } elseif (SaleSources::SOURCE_COMMERCIAL === $sale->getSource()) {
            $flags .= '<i class="fa fa-briefcase"></i>';
        }
        if (!empty($sale->getComment())) {
            $flags .= '<i class="fa fa-comment"></i>';
        }

        return $flags;
    }
}
