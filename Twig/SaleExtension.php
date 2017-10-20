<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CoreBundle\Twig\UiExtension;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\TransformationTargets;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Document\Util\SaleDocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SaleExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var UiExtension
     */
    private $uiExtension;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param ConstantsHelper       $constantHelper
     * @param ViewBuilder           $viewBuilder
     * @param UiExtension           $uiExtension
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        ViewBuilder $viewBuilder,
        UiExtension $uiExtension,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->constantHelper = $constantHelper;
        $this->viewBuilder = $viewBuilder;
        $this->uiExtension = $uiExtension;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest(
                'sale_stockable_state',
                [$this, 'isSaleStockableSale']
            )
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'sale_state_label',
                [$this->constantHelper, 'renderSaleStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'sale_state_badge',
                [$this->constantHelper, 'renderSaleStateBadge'],
                ['is_safe' => ['html']]
            ),
            // Builds the sale view form the sale
            new \Twig_SimpleFilter(
                'sale_view',
                [$this->viewBuilder, 'buildSaleView']
            ),
            new \Twig_SimpleFilter(
                'sale_editable_document_types',
                [SaleDocumentUtil::class, 'getSaleEditableDocumentTypes']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            // Renders the sale view
            new \Twig_SimpleFunction(
                'render_sale_view',
                [$this, 'renderSaleView'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            // Renders the sale transform button
            new \Twig_SimpleFunction(
                'sale_transform_btn',
                [$this, 'renderSaleTransformButton'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns whether the sale is in a stockable state.
     *
     * @param SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleStockableSale(SaleInterface $sale)
    {
        if ($sale instanceof OrderInterface) {
            return OrderStates::isStockableState($sale->getState());
        }

        return false;
    }

    /**
     * Renders the sale view.
     *
     * @param \Twig_Environment $env
     * @param SaleView          $view
     * @param string            $template TODO remove as defined in view vars
     *
     * @return string
     */
    public function renderSaleView(\Twig_Environment $env, SaleView $view, $template = 'EkynaCommerceBundle:Common:sale_view.html.twig')
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpInternalEntityUsedInspection */
        return $env->loadTemplate($template)->renderBlock('sale', ['view' => $view]);
    }

    /**
     * Renders the sale transform button.
     *
     * @param SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleTransformButton(SaleInterface $sale)
    {
        $actions = [];

        // TODO use constants for target

        $targets = TransformationTargets::getTargetsForSale($sale);

        if ($sale instanceof CartInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_cart_admin_transform', [
                        'cartId' => $sale->getId(),
                        'target' => $target,
                    ]);
            }
        } elseif ($sale instanceof QuoteInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_quote_admin_transform', [
                        'quoteId' => $sale->getId(),
                        'target'  => $target,
                    ]);
            }
        } elseif ($sale instanceof OrderInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_order_admin_transform', [
                        'orderId' => $sale->getId(),
                        'target'  => $target,
                    ]);
            }
        } else {
            throw new InvalidArgumentException("Unsupported sale type.");
        }

        return $this
            ->uiExtension
            ->renderButtonDropdown('ekyna_core.button.transform', $actions);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_sale';
    }
}
