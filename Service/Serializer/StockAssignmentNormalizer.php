<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\StockAssignmentNormalizer as BaseNormalizer;

/**
 * Class StockAssignmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentNormalizer extends BaseNormalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($assignment, $format = null, array $context = [])
    {
        $data = parent::normalize($assignment, $format, $context);

        $groups = isset($context['groups']) ? (array)$context['groups'] : [];

        if (in_array('StockView', $groups)) {
            $data = array_replace($data, [
                'ready'   => $assignment->isFullyShipped() || $assignment->isFullyShippable(),
                'actions' => [],
            ]);
        }

        return $data;
    }
}