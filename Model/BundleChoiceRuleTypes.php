<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CoreBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Product\Model\BundleChoiceRuleTypes as Types;

/**
 * Class BundleChoiceRuleTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BundleChoiceRuleTypes extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.bundle_choice_rule.type.';

        return [
            Types::TYPE_DISABLED => [$prefix . Types::TYPE_DISABLED],
            Types::TYPE_OPTIONAL => [$prefix . Types::TYPE_OPTIONAL],
        ];
    }
}
