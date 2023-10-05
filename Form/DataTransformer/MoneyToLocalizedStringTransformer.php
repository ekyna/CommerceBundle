<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer as BaseTransformer;

/**
 * Class MoneyToLocalizedStringTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MoneyToLocalizedStringTransformer extends BaseTransformer
{
    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        // TODO Refreshing a form FLOW gives a Decimal here

        if ($value) {
            $value = str_replace(' ', '', $value); // \u202F
        }

        return parent::reverseTransform($value);
    }
}
