<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderAttachmentTypes;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class SupplierExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierExtension extends AbstractExtension
{
    /**
     * @var SupplierOrderCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param SupplierOrderCalculatorInterface $calculator
     */
    public function __construct(SupplierOrderCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'supplier_order_attachment_types',
                [SupplierOrderAttachmentTypes::class, 'getChoices']
            )
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'supplier_order_state_label',
                [ConstantsHelper::class, 'renderSupplierOrderStateLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'supplier_order_state_badge',
                [ConstantsHelper::class, 'renderSupplierOrderStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'supplier_order_weight_total',
                [$this->calculator, 'calculateWeightTotal']
            ),
            new TwigFilter(
                'supplier_order_items_total',
                [$this->calculator, 'calculateItemsTotal']
            ),
            new TwigFilter(
                'supplier_order_attachment_type_label',
                [ConstantsHelper::class, 'renderSupplierOrderAttachmentType'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return [
            new TwigTest('new_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_NEW;
            }),
            new TwigTest('ordered_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_ORDERED;
            }),
            new TwigTest('validated_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_VALIDATED;
            }),
            new TwigTest('partial_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_PARTIAL;
            }),
            new TwigTest('canceled_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_CANCELED;
            }),
            new TwigTest('received_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_RECEIVED;
            }),
            new TwigTest('completed_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_COMPLETED;
            }),
            new TwigTest('cancelable_supplier_order', function (SupplierOrderInterface $order) {
                return SupplierOrderStates::isCancelableState($order);
            }),
            new TwigTest('deleteable_supplier_order', function (SupplierOrderInterface $order) {
                return SupplierOrderStates::isDeletableState($order);
            }),
            new TwigTest('stockable_supplier_order', function (SupplierOrderInterface $order) {
                return SupplierOrderStates::isStockableState($order);
            }),
        ];
    }
}
