<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CommerceBundle\Model\OrderStates;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAssignmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Common\Util\Formatter;

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
     * @inheritDoc
     *
     * @param \Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface $assignment
     */
    public function normalize($assignment, $format = null, array $context = [])
    {
        $data = parent::normalize($assignment, $format, $context);

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        $actions = [];

        if (in_array('StockAssignment', $groups)) {
            $data = array_replace($data, [
                'ready'   => $assignment->isFullyShipped() || $assignment->isFullyShippable(),
            ]);
        } elseif (in_array('StockView', $groups)) {
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