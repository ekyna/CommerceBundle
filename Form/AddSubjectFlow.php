<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;

/**
 * Class AddSubjectFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddSubjectFlow extends FormFlow
{
    /**
     * @inheritdoc
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label'     => 'type',
                'form_type' => Type\SubjectChoiceType::class,
                'form_options' => [
                    'validation_groups' => ['flow_step_1'],
                ]
            ],
            [
                'label'     => 'choice',
                'form_type' => Type\SubjectChoiceType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ]
                /* TODO skip if 'manual'
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return $estimatedCurrentStepNumber > 1 && !$flow->getFormData()->canHaveEngine();
                },*/
            ],
        ];
    }
}
