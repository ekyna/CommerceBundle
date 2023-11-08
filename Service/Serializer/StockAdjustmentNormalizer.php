<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit\DeleteAdjustmentAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit\UpdateAdjustmentAction;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAdjustmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;

/**
 * Class StockAdjustmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentNormalizer extends BaseNormalizer
{
    public function __construct(
        FormatterFactory                   $formatterFactory,
        protected readonly ConstantsHelper $constantHelper,
        protected readonly ResourceHelper  $resourceHelper
    ) {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @inheritDoc
     *
     * @param StockAdjustmentInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup(Group::STOCK_UNIT, $context)) {
            $update = $this
                ->resourceHelper
                ->generateResourcePath($object->getStockUnit(), UpdateAdjustmentAction::class, [
                    'adjustmentId' => $object->getId(),
                ]);

            $delete = $this
                ->resourceHelper
                ->generateResourcePath($object->getStockUnit(), DeleteAdjustmentAction::class, [
                    'adjustmentId' => $object->getId(),
                ]);

            $actions = [
                [
                    'label' => '<i class="fa fa-pencil"></i>',
                    'href'  => $update,
                    'theme' => 'warning',
                    'modal' => true,
                ],
                [
                    'label' => '<i class="fa fa-remove"></i>',
                    'href'  => $delete,
                    'theme' => 'danger',
                    'modal' => true,
                ],
            ];

            $data = array_replace($data, [
                'reason_label' => $this->constantHelper->renderStockAdjustmentReasonLabel($object),
                'type_label'   => $this->constantHelper->renderStockAdjustmentTypeLabel($object),
                'type_badge'   => $this->constantHelper->renderStockAdjustmentTypeBadge($object),
                'actions'      => $actions,
            ]);
        }

        return $data;
    }
}
