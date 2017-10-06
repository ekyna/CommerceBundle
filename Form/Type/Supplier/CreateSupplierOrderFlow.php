<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

/**
 * Class CreateSupplierOrderFlow
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateSupplierOrderFlow extends FormFlow
{
    /**
     * @inheritdoc
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label'     => 'supplier',
                'form_type' => SupplierOrderType::class,
                'skip'      => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
                    $supplierOrder = $flow->getFormData();
                    return $estimatedCurrentStepNumber == 1 && null !== $supplierOrder->getSupplier();
                },
            ],
            [
                'label'     => 'configuration',
                'form_type' => SupplierOrderType::class,
            ],
        ];
    }
}
