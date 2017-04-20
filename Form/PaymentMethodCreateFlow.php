<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodFactoryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodType;

/**
 * Class PaymentMethodCreateFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodCreateFlow extends FormFlow
{
    protected function loadStepsConfig(): array
    {
        return [
            [
                'label'        => 'factory',
                'form_type'    => PaymentMethodFactoryChoiceType::class,
            ],
            [
                'label'        => 'config',
                'form_type'    => PaymentMethodType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
            ],
        ];
    }
}
