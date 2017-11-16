<?php

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodFactoryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodType;

/**
 * Class ShipmentMethodCreateFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodCreateFlow extends FormFlow
{
    /**
     * @inheritdoc
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label'        => 'factory',
                'form_type'    => ShipmentMethodFactoryChoiceType::class,
            ],
            [
                'label'        => 'config',
                'form_type'    => ShipmentMethodType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
            ],
        ];
    }
}
