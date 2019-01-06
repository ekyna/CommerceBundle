<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAdjustmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;

/**
 * Class StockAdjustmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentNormalizer extends BaseNormalizer
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
     * @param StockAdjustmentInterface $adjustment
     */
    public function normalize($adjustment, $format = null, array $context = [])
    {
        $data = parent::normalize($adjustment, $format, $context);

        if ($this->contextHasGroup('StockView', $context)) {
            $actions = [
                [
                    'label' => '<i class="fa fa-pencil"></i>',
                    'href'  => $this->resourceHelper->generateResourcePath($adjustment->getStockUnit(), 'adjustment_edit', [
                        'stockAdjustmentId' => $adjustment->getId(),
                    ]),
                    'theme' => 'warning',
                    'modal' => true,
                ],
                [
                    'label' => '<i class="fa fa-remove"></i>',
                    'href'  => $this->resourceHelper->generateResourcePath($adjustment->getStockUnit(), 'adjustment_remove', [
                        'stockAdjustmentId' => $adjustment->getId(),
                    ]),
                    'theme' => 'danger',
                    'modal' => true,
                ],
            ];

            $data = array_replace($data, [
                'reason_label' => $this->constantHelper->renderStockAdjustmentReasonLabel($adjustment),
                'type_label'   => $this->constantHelper->renderStockAdjustmentTypeLabel($adjustment),
                'type_badge'   => $this->constantHelper->renderStockAdjustmentTypeBadge($adjustment),
                'actions'      => $actions,
            ]);
        }

        return $data;
    }
}