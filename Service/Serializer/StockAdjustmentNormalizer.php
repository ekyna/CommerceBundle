<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit\DeleteAdjustmentAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\StockUnit\UpdateAdjustmentAction;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
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
    protected ConstantsHelper $constantHelper;
    protected ResourceHelper  $resourceHelper;

    public function __construct(
        FormatterFactory $formatterFactory,
        ConstantsHelper  $constantHelper,
        ResourceHelper   $resourceHelper
    ) {
        $this->formatterFactory = $formatterFactory;
        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @inheritDoc
     *
     * @param StockAdjustmentInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup('StockView', $context)) {
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
