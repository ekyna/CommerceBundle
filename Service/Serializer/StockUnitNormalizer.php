<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockUnitNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockUnitNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitNormalizer extends BaseNormalizer
{
    /**
     * @var ConstantsHelper
     */
    protected $constantHelper;

    /**
     * @var ResourceHelper
     */
    protected $resourceHelper;


    /**
     * Constructor.
     *
     * @param Formatter       $formatter
     * @param ConstantsHelper $constantHelper
     * @param ResourceHelper  $resourceHelper
     */
    public function __construct(Formatter $formatter, ConstantsHelper $constantHelper, ResourceHelper $resourceHelper)
    {
        parent::__construct($formatter);

        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @inheritdoc
     *
     * @param StockUnitInterface $unit
     */
    public function normalize($unit, $format = null, array $context = [])
    {
        $data = parent::normalize($unit, $format, $context);

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('StockView', $groups) || in_array('StockAssignment', $groups)) {
            $translator = $this->constantHelper->getTranslator();

            if (null === $eda = $data['eda']) {
                $eda = '<em>' . $translator->trans('ekyna_core.value.undefined') . '</em>';
            }

            $actions = [];

            if (in_array('StockView', $groups)) {
                if (null !== $supplierOrderItem = $unit->getSupplierOrderItem()) {
                    $supplierOrder = $supplierOrderItem->getOrder();

                    $actions[] = [
                        'label' => sprintf('%s (%s)',
                            $supplierOrder->getNumber(),
                            $this->constantHelper->renderSupplierOrderStateLabel($supplierOrder)
                        ),
                        'href'  => $this->resourceHelper->generateResourcePath($supplierOrder),
                        'theme' => SupplierOrderStates::getTheme($supplierOrder->getState()),
                        'modal' => false,
                    ];
                }

                $actions[] = [
                    'label' => '<i class="fa fa-pencil"></i>',
                    'href'  => $this->resourceHelper->generateResourcePath($unit, 'adjustment_new'),
                    'theme' => 'success',
                    'modal' => true,
                ];
            }

            $data = array_replace($data, [
                'state_label' => $this->constantHelper->renderStockUnitStateLabel($unit),
                'state_badge' => $this->constantHelper->renderStockUnitStateBadge($unit),
                'eda'         => $eda,
                'actions'     => $actions,
            ]);
        }

        return $data;
    }
}