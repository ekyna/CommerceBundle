<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit\CreateAdjustmentAction;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockUnitNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockUnitNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitNormalizer extends BaseNormalizer
{
    protected ConstantsHelper $constantHelper;
    protected ResourceHelper $resourceHelper;

    public function __construct(
        FormatterFactory $formatterFactory,
        CurrencyConverterInterface $currencyConverter,
        ConstantsHelper $constantHelper,
        ResourceHelper $resourceHelper
    ) {
        parent::__construct($formatterFactory, $currencyConverter);

        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @inheritDoc
     *
     * @param StockUnitInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup(['StockView', 'StockAssignment'], $context)) {
            $translator = $this->constantHelper->getTranslator();

            if (null === $eda = $data['eda']) {
                $eda = '<em>' . $translator->trans('value.undefined', [], 'EkynaUi') . '</em>';
            }

            $actions = [];

            if ($this->contextHasGroup('StockView', $context)) {
                if (null !== $supplierOrderItem = $object->getSupplierOrderItem()) {
                    $supplierOrder = $supplierOrderItem->getOrder();

                    $actions[] = [
                        'label' => sprintf('%s (%s)',
                            $supplierOrder->getNumber(),
                            $this->constantHelper->renderSupplierOrderStateLabel($supplierOrder)
                        ),
                        'href'  => $this->resourceHelper->generateResourcePath($supplierOrder, ReadAction::class),
                        'theme' => SupplierOrderStates::getTheme($supplierOrder->getState()),
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
