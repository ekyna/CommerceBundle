<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Event\SaleButtonsEvent;
use Ekyna\Bundle\CoreBundle\Model\UiButton;
use Ekyna\Bundle\CoreBundle\Service\Ui\UiRenderer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param EngineInterface          $templating
     * @param EventDispatcherInterface $dispatcher
     * @param UiRenderer               $uiRenderer
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        EngineInterface $templating,
        EventDispatcherInterface $dispatcher,
        UiRenderer $uiRenderer,
        TranslatorInterface $translator
    ) {
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->uiRenderer = $uiRenderer;
        $this->translator = $translator;
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

        usort($buttons, function (UiButton $a, UiButton $b) {
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
     * @param array         $options
     *
     * @return string
     */
    public function renderSaleFlags(SaleInterface $sale, array $options = [])
    {
        $options = array_replace([
            'badge' => true,
        ], $options);

        $template = $options['badge']
            ? '<span title="%s" class="label label-%s"><i class="fa fa-%s"></i></span>'
            : '<i title="%s" class="text-%s fa fa-%s"></i>';

        $flags = '';

        if (SaleSources::SOURCE_WEBSITE === $sale->getSource()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.source.website'),
                'default',
                'sitemap'
            );
        } elseif (SaleSources::SOURCE_COMMERCIAL === $sale->getSource()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.source.commercial'),
                'default',
                'briefcase'
            );
        } // TODO marketplace

        if ($sale instanceof OrderInterface && $sale->isFirst()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.flag.first_order'),
                'success',
                'thumbs-o-up'
            );
        }

        if ($sale->isSample()) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.field.sample'),
                'info',
                'cube'
            );

            if ($sale->canBeReleased()) {
                $flags .= sprintf(
                    $template,
                    $this->translator->trans('ekyna_commerce.sale.flag.can_be_released'),
                    'danger',
                    'check-circle-o'
                );
            }
        }

        if (!empty($sale->getPreparationNote())) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_commerce.sale.field.preparation_note'),
                'warning',
                'check-square-o'
            );
        }

        if (!empty($sale->getComment())) {
            $flags .= sprintf(
                $template,
                $this->translator->trans('ekyna_core.field.comment'),
                'danger',
                'comment'
            );
        }

        return $flags;
    }
}
