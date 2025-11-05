<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierProductType;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;

/**
 * Class SupplierProductCreateFlow
 * @package Ekyna\Bundle\CommerceBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductCreateFlow extends FormFlow
{
    protected function loadStepsConfig(): array
    {
        return [
            [
                'label'     => 'supplier',
                'form_type' => SupplierProductType::class,
                'skip'      => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    /** @var SupplierProductInterface $supplierProduct */
                    $supplierProduct = $flow->getFormData();

                    return $estimatedCurrentStepNumber == 1 && null !== $supplierProduct->getSupplier();
                },
            ],
            [
                'label'        => 'configuration',
                'form_type'    => SupplierProductType::class,
                'form_options' => [
                    'validation_groups' => ['Default'],
                ],
            ],
        ];
    }
}
