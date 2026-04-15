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

use function array_intersect;
use function Symfony\Component\Translation\t;

/**
 * Class TwigHelper
 * @package Ekyna\Bundle\CommerceBundle\Twig\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleRenderer
{
    public function __construct(
        private readonly ContextProviderInterface $contextProvider,
        private readonly ViewBuilder              $viewBuilder,
        private readonly Environment              $twig,
        private readonly UiRenderer               $uiRenderer,
        private readonly ResourceHelper           $resourceHelper
    ) {
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
    public function renderSaleDuplicateButton(Common\SaleInterface $sale, array $targets = []): string
    {
        return $this->renderSaleOperationButton($sale, 'duplicate', $targets);
    }

    /**
     * Renders the sale transform button.
     */
    public function renderSaleTransformButton(Common\SaleInterface $sale, array $targets = []): string
    {
        return $this->renderSaleOperationButton($sale, 'transform', $targets);
    }

    /**
     * Renders the sale export button.
     */
    public function renderSaleExportButton(Common\SaleInterface $sale, array $restrict = []): string
    {
        $actions = [];

        $entries = [
            'csv'          => [
                'name'       => 'CSV',
                'parameters' => ['_format' => 'csv'],
            ],
            'xls'          => [
                'name'       => 'Excel',
                'parameters' => ['_format' => 'xls'],
            ],
            'internal_xls' => [
                'name'       => 'Excel (interne)',
                'parameters' => ['_format' => 'xls', 'internal' => 1],
            ],
        ];

        foreach ($entries as $key => $config) {
            if (!empty($restrict) && !in_array($key, $restrict, true)) {
                continue;
            }

            $path = $this->resourceHelper->generateResourcePath($sale, ExportAction::class, $config['parameters']);

            $actions[$path] = $config['name'];
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
    private function renderSaleOperationButton(
        Common\SaleInterface $sale,
        string               $operation,
        array                $restrict = []
    ): string
    {
        $actions = [];

        $targets = Common\TransformationTargets::getTargetsForSale($sale, $operation === 'duplicate');

        if (!empty($restrict)) {
            $targets = array_intersect($targets, $restrict);
        }

        if (empty($targets)) {
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
