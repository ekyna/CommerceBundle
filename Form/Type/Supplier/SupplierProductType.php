<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('reference', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ])
            ->add('netPrice', Symfony\NumberType::class, [
                'label' => 'ekyna_core.field.price',
                'scale' => 2,
                'attr'  => [
                    'input_group' => ['append' => '€'], // TODO currency
                ],
            ])
            ->add('weight', Symfony\NumberType::class, [
                'label'    => 'ekyna_core.field.weight',
                'scale'    => 2,
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'Kg'],
                ],
            ])
            ->add('availableStock', Symfony\NumberType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.available_stock',
                'scale'    => 3,
                'required' => false,
            ])
            ->add('orderedStock', Symfony\NumberType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.ordered_stock',
                'scale'    => 3,
                'required' => false,
            ])
            ->add('estimatedDateOfArrival', Symfony\DateTimeType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.estimated_date_of_arrival',
                'required' => false,
            ])
            ->add('subjectIdentity', Commerce\Subject\SubjectChoiceType::class, [
                'label'     => 'ekyna_commerce.supplier_product.field.subject',
                'lock_mode' => true,
                'required'  => false,
                'context'   => SubjectProviderInterface::CONTEXT_SUPPLIER,
            ]);
    }
}
