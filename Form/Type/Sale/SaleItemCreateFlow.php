<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Craue\FormFlowBundle\Form\FormFlow;
use Ekyna\Bundle\CommerceBundle\Form\Type;

/**
 * Class SaleItemCreateFlow
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemCreateFlow extends FormFlow
{
    protected function loadStepsConfig(): array
    {
        return [
            [
                'label'        => 'choice',
                'form_type'    => Type\Sale\SaleItemSubjectType::class,
                'form_options' => [
                    'required'          => true,
                    'validation_groups' => [
                        'sale_item_create_flow_choice',
                    ],
                ],
            ],
            [
                'label'        => 'configure',
                'form_type'    => Type\Sale\SaleItemConfigureType::class,
                'form_options' => [
                    'validation_groups' => [
                        'sale_item_create_flow_configure',
                        'Default',
                    ],
                ],
            ],
        ];
    }
}
