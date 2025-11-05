<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderStates;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAssignmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Helper\EnumHelper;

use function is_null;
use function sprintf;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends BaseNormalizer
{
    public function __construct(
        FormatterFactory                   $formatterFactory,
        protected readonly ConstantsHelper $constantHelper,
        protected readonly EnumHelper      $enumHelper,
        protected readonly ResourceHelper  $resourceHelper
    ) {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @inheritDoc
     *
     * @param AssignmentInterface $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        $data['actions'] = [];

        if (self::contextHasGroup([Group::STOCK_UNIT], $context)) {
            $assignable = $object->getAssignable();

            if ($assignable instanceof OrderItemInterface) {
                $this->addOrderData($data, $assignable->getRootSale());
            } elseif ($assignable instanceof ProductionItemInterface) {
                $this->addProductionOrderData($data, $assignable->getProductionOrder());
            }

            return $data;
        }

        if (self::contextHasGroup(Group::STOCK_ASSIGNMENT, $context)) {
            $stockUnit = $object->getStockUnit();

            if ($order = $stockUnit->getSupplierOrder()) {
                $this->addSupplierOrderData($data, $order);
            } elseif ($order = $stockUnit->getProductionOrder()) {
                $this->addProductionOrderData($data, $order);
            }
        }

        return $data;
    }

    private function addOrderData(array &$data, OrderInterface $order): void
    {
        $data['summary'] = $this->resourceHelper->generateResourcePath($order, SummaryAction::class);
        $data['actions'] = [
            [
                'label' => sprintf(
                    '%s (%s)',
                    $order->getNumber(),
                    $this->constantHelper->renderOrderStateLabel($order)
                ),
                'href'  => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                'theme' => OrderStates::getTheme($order->getState()),
                'modal' => false,
            ],
        ];
    }

    private function addSupplierOrderData(array &$data, ?SupplierOrderInterface $order): void
    {
        if (is_null($order)) {
            return;
        }

        $data['actions'] = [
            [
                'label' => sprintf(
                    '%s (%s)',
                    $order->getNumber(),
                    $this->constantHelper->renderSupplierOrderStateLabel($order)
                ),
                'href'  => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                'theme' => SupplierOrderStates::getTheme($order->getState()),
                'modal' => false,
            ],
        ];
    }

    private function addProductionOrderData(array &$data, ProductionOrderInterface $order): void
    {
        $data['actions'] = [
            [
                'label' => sprintf(
                    '%s (%s)',
                    $order->getNumber(),
                    $this->enumHelper->label($order->getState())
                ),
                'href'  => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                'theme' => $order->getState()->color(),
                'modal' => false,
            ],
        ];
    }
}
