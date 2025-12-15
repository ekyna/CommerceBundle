<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

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
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow): bool {
                    $data = $flow->getFormData();
                    if (!$data instanceof SaleItemInterface) {
                        return false;
                    }

                    return $data->getSubjectIdentity()->hasIdentity();
                },
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
