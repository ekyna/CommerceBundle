<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TaxType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxType extends ResourceFormType
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
            ->add('rate', Type\NumberType::class, [
                'label' => 'ekyna_core.field.rate',
                'attr' => [
                    'input_group' => ['append' => '%'],
                ],
            ])
            ->add('country', CountryChoiceType::class, [
                'enabled' => false,
            ])
            /*TODO->add('state', ResourceType::class, [
                'label' => 'ekyna_commerce.state.label.singular',
                'class' => $this->stateClass,
            ])*/;
    }
}
