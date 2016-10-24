<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Subject\Helper;

/**
 * Class SubjectHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectHelper extends Helper implements SubjectHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptions(SaleItemInterface $item, $property)
    {
        if ($item->hasChildren() && in_array($property, ['netPrice', 'weight', 'taxGroup'])) {
            return [
                'disabled' => true,
            ];
        }

        return [];
    }
}
