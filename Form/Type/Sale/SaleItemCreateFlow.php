<?php

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
    /**
     * @inheritdoc
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label'        => 'choice',
                'form_type'    => Type\Sale\SaleItemSubjectChoiceType::class,
                'form_options' => [
                    'required' => true,
                    'validation_groups' => [
                        'sale_item_create_flow_choice'
                    ],
                ],
            ],
            [
                'label'        => 'configure',
                'form_type'    => Type\Sale\SaleItemSubjectConfigureType::class,
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
