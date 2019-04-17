<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TaxRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('priority', Type\NumberType::class, [
                'label' => 'ekyna_core.field.priority',
            ])
            ->add('customer', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.tax_rule.field.customer',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('business', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.tax_rule.field.business',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('countries', CountryChoiceType::class, [
                'enabled'  => false,
                'multiple' => true,
            ])
            ->add('taxes', TaxChoiceType::class, [
                'multiple'  => true,
                'allow_new' => true,
            ])
            ->add('notices', CollectionType::class, [
                'label'        => 'ekyna_commerce.tax_rule.field.notices',
                'allow_add'    => true,
                'allow_delete' => true,
                'allow_sort'   => true,
            ]);
    }
}
