<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Model\OrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAssignmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends BaseNormalizer
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
     * @param FormatterFactory $formatterFactory
     * @param ConstantsHelper  $constantHelper
     * @param ResourceHelper   $resourceHelper
     */
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
     * @param \Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface $assignment
     */
    public function normalize($assignment, $format = null, array $context = [])
    {
        $data = parent::normalize($assignment, $format, $context);

        $actions = [];

        if ($this->contextHasGroup('StockView', $context)) {
            $order = $assignment->getSaleItem()->getSale();

            $actions[] = [
                'label' => sprintf('%s (%s)',
                    $order->getNumber(),
                    $this->constantHelper->renderOrderStateLabel($order)
                ),
                'href'  => $this->resourceHelper->generateResourcePath($order),
                'theme' => OrderStates::getTheme($order->getState()),
                'modal' => false,
            ];
        }

        $data['actions'] = $actions;

        return $data;
    }
}
