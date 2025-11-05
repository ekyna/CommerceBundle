<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit\CreateAdjustmentAction;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockUnitNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Helper\EnumHelper;

/**
 * Class StockUnitNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitNormalizer extends BaseNormalizer
{
    public function __construct(
        FormatterFactory                   $formatterFactory,
        CurrencyConverterInterface         $currencyConverter,
        protected readonly ConstantsHelper $constantHelper,
        protected readonly EnumHelper      $enumHelper,
        protected readonly ResourceHelper  $resourceHelper
    ) {
        parent::__construct($formatterFactory, $currencyConverter);
    }

    /**
     * @inheritDoc
     *
     * @param StockUnitInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup([Group::STOCK_UNIT, Group::STOCK_ASSIGNMENT], $context)) {
            $translator = $this->constantHelper->getTranslator();

            if (null === $eda = $data['eda']) {
                $eda = '<em>' . $translator->trans('value.undefined', [], 'EkynaUi') . '</em>';
            }

            $actions = [];

            if (self::contextHasGroup(Group::STOCK_UNIT, $context)) {
                if (null !== $item = $object->getSupplierOrderItem()) {
                    $order = $item->getOrder();

                    $actions[] = [
                        'label' => sprintf(
                            '%s (%s)',
                            $order->getNumber(),
                            $this->constantHelper->renderSupplierOrderStateLabel($order)
                        ),
                        'href'  => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                        'theme' => SupplierOrderStates::getTheme($order->getState()),
                        'modal' => false,
                    ];
                } elseif (null !== $order = $object->getProductionOrder()) {
                    $actions[] = [
                        'label' => sprintf(
                            '%s (%s)',
                            $order->getNumber(),
                            $this->enumHelper->label($order->getState())
                        ),
                        'href'  => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                        'theme' => $order->getState()->color(),
                        'modal' => false,
                    ];
                }

                $actions[] = [
                    'label' => '<i class="fa fa-pencil"></i>',
                    'href'  => $this->resourceHelper->generateResourcePath($object, CreateAdjustmentAction::class),
                    'theme' => 'success',
                    'modal' => true,
                ];
            }

            $data = array_replace($data, [
                'state_label' => $this->constantHelper->renderStockUnitStateLabel($object),
                'state_badge' => $this->constantHelper->renderStockUnitStateBadge($object),
                'eda'         => $eda,
                'actions'     => $actions,
            ]);
        }

        return $data;
    }
}
