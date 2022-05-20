<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\CommerceBundle\Model\OrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAssignmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends BaseNormalizer
{
    protected ConstantsHelper $constantHelper;
    protected ResourceHelper $resourceHelper;

    public function __construct(
        FormatterFactory $formatterFactory,
        ConstantsHelper $constantHelper,
        ResourceHelper $resourceHelper
    ) {
        $this->formatterFactory = $formatterFactory;
        $this->constantHelper = $constantHelper;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @inheritDoc
     *
     * @param StockAssignmentInterface $object
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = parent::normalize($object, $format, $context);

        $actions = [];

        if ($this->contextHasGroup('StockView', $context)) {
            $order = $object->getSaleItem()->getRootSale();

            $actions[] = [
                'label' => sprintf('%s (%s)',
                    $order->getNumber(),
                    $this->constantHelper->renderOrderStateLabel($order)
                ),
                'href'  => $this->resourceHelper->generateResourcePath($order, ReadAction::class),
                'theme' => OrderStates::getTheme($order->getState()),
                'modal' => false,
            ];
        }

        $data['actions'] = $actions;

        return $data;
    }
}
