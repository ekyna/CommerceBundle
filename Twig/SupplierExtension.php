<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderAttachmentTypes;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;

/**
 * Class SupplierExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var SupplierOrderCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param ConstantsHelper                  $constantHelper
     * @param SupplierOrderCalculatorInterface $calculator
     */
    public function __construct(ConstantsHelper $constantHelper, SupplierOrderCalculatorInterface $calculator)
    {
        $this->constantHelper = $constantHelper;
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
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
            new \Twig_SimpleFilter(
                'supplier_order_state_label',
                [$this->constantHelper, 'renderSupplierOrderStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'supplier_order_state_badge',
                [$this->constantHelper, 'renderSupplierOrderStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'supplier_order_weight_total',
                [$this->calculator, 'calculateWeightTotal']
            ),
            new \Twig_SimpleFilter(
                'supplier_order_items_total',
                [$this->calculator, 'calculateItemsTotal']
            ),
            new \Twig_SimpleFilter(
                'supplier_order_attachment_type_label',
                [$this->constantHelper, 'renderSupplierOrderAttachmentType'],
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
            new \Twig_SimpleTest('new_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_NEW;
            }),
            new \Twig_SimpleTest('ordered_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_ORDERED;
            }),
            new \Twig_SimpleTest('partial_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_PARTIAL;
            }),
            new \Twig_SimpleTest('canceled_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_CANCELED;
            }),
            new \Twig_SimpleTest('received_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_RECEIVED;
            }),
            new \Twig_SimpleTest('completed_supplier_order', function (SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_COMPLETED;
            }),
        ];
    }
}
