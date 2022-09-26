<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\DuplicateAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\ExportAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\TransformAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\UiBundle\Service\UiRenderer;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class TwigHelper
 * @package Ekyna\Bundle\CommerceBundle\Twig\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleRenderer
{
    private ContextProviderInterface $contextProvider;
    private ViewBuilder $viewBuilder;
    private Environment $twig;
    private UiRenderer     $uiRenderer;
    private ResourceHelper $resourceHelper;


    public function __construct(
        ContextProviderInterface $contextProvider,
        ViewBuilder $viewBuilder,
        Environment $twig,
        UiRenderer $uiRenderer,
        ResourceHelper $resourceHelper
    ) {
        $this->contextProvider = $contextProvider;
        $this->viewBuilder = $viewBuilder;
        $this->twig = $twig;
        $this->uiRenderer = $uiRenderer;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * Returns the sale shipment (net or ati regarding to the sale's context).
     */
    public function getSaleShipmentAmount(Common\SaleInterface $sale): Decimal
    {
        $amount = $net = $sale->getShipmentAmount();

        if ($this->contextProvider->getContext($sale)->isAtiDisplayMode()) {
            foreach ($sale->getAdjustments(Common\AdjustmentTypes::TYPE_TAXATION) as $adjustment) {
                $amount += $net->mul($adjustment->getAmount())->div(100);
            }
        }

        return $amount;
    }

    /**
     * @deprecated Use SaleViewHelper
     */
    public function buildSaleView(Common\SaleInterface $sale, array $options = []): SaleView
    {
        return $this->viewBuilder->buildSaleView($sale, $options);
    }

    public function renderSaleView(SaleView $view, string $template = null): string
    {
        if (empty($template)) {
            $template = $view->template;
        }

        return $this->twig->load($template)->renderBlock('sale', ['view' => $view]);
    }

    /**
     * Renders the sale transform button.
     */
    public function renderSaleDuplicateButton(Common\SaleInterface $sale): string
    {
        return $this->renderSaleOperationButton($sale, 'duplicate');
    }

    /**
     * Renders the sale transform button.
     */
    public function renderSaleTransformButton(Common\SaleInterface $sale): string
    {
        return $this->renderSaleOperationButton($sale, 'transform');
    }

    /**
     * Renders the sale export button.
     */
    public function renderSaleExportButton(Common\SaleInterface $sale): string
    {
        $actions = [];

        $formats = ['CSV' => 'csv', 'Excel' => 'xls'];

        foreach ($formats as $name => $format) {
            $path = $this->resourceHelper->generateResourcePath($sale, ExportAction::class, [
                '_format' => $format,
            ]);

            $actions[$path] = $name;
        }

        return $this
            ->uiRenderer
            ->renderDropdown($actions, [
                'label'        => 'button.export',
                'icon'         => 'download',
                'trans_domain' => 'EkynaUi',
            ]);
    }

    /**
     * Renders the sale operation dropdown.
     */
    private function renderSaleOperationButton(Common\SaleInterface $sale, string $operation): string
    {
        $actions = [];

        if (empty($targets = Common\TransformationTargets::getTargetsForSale($sale, $operation === 'duplicate'))) {
            return '';
        }

        if ($operation === 'duplicate') {
            $action = DuplicateAction::class;
        } elseif ($operation === 'transform') {
            $action = TransformAction::class;
        } else {
            throw new InvalidArgumentException('Unsupported operation.');
        }

        foreach ($targets as $target) {
            $path = $this->resourceHelper->generateResourcePath($sale, $action, [
                'target' => $target,
            ]);

            $actions[$path] = t($target . '.label.singular', [], 'EkynaCommerce');
        }

        return $this
            ->uiRenderer
            ->renderDropdown($actions, [
                'label'        => 'button.' . $operation,
                'trans_domain' => 'EkynaUi',
                'icon'         => $operation === 'duplicate' ? 'clone' : 'magic',
                'fa_icon'      => true,
            ]);
    }
}
