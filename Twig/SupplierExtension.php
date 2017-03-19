<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
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
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('new_supplier_order', function(SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_NEW;
            }),
            new \Twig_SimpleTest('ordered_supplier_order', function(SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_ORDERED;
            }),
            new \Twig_SimpleTest('partial_supplier_order', function(SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_PARTIAL;
            }),
            new \Twig_SimpleTest('cancelled_supplier_order', function(SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_CANCELLED;
            }),
            new \Twig_SimpleTest('completed_supplier_order', function(SupplierOrderInterface $order) {
                return $order->getState() === SupplierOrderStates::STATE_COMPLETED;
            }),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_supplier';
    }
}
