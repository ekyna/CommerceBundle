<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TaxType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxInterface $tax */
            $tax = $event->getData();
            $form = $event->getForm();

            $disabled = !empty($tax->getCode());

            $form
                ->add('name', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.name',
                    'disabled' => $disabled,
                ])
                ->add('rate', Type\NumberType::class, [
                    'label'    => 'ekyna_core.field.rate',
                    'disabled' => $disabled,
                    'attr'     => [
                        'input_group' => ['append' => '%'],
                    ],
                ])
                ->add('country', CountryChoiceType::class, [
                    'enabled'  => false,
                    'disabled' => $disabled,
                ])
                /*TODO->add('state', ResourceType::class, [
                    'label' => 'ekyna_commerce.state.label.singular',
                    'class' => $this->stateClass,
                ])*/
            ;
        });
    }
}
