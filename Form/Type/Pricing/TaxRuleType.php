<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionsType;
use Ekyna\Component\Commerce\Pricing\Entity;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TaxRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface $rule */
            $rule = $event->getData();
            $form = $event->getForm();

            $disabled = !empty($rule->getCode());

            $form
                ->add('name', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.name',
                    'disabled' => $disabled,
                ])
                ->add('priority', Type\NumberType::class, [
                    'label'    => 'ekyna_core.field.priority',
                    'disabled' => $disabled,
                ])
                ->add('customer', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.tax_rule.field.customer',
                    'required' => false,
                    'disabled' => $disabled,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('business', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.tax_rule.field.business',
                    'required' => false,
                    'disabled' => $disabled,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('sources', CountryChoiceType::class, [
                    'label'    => 'ekyna_commerce.tax_rule.field.sources',
                    'enabled'  => false,
                    'multiple' => true,
                    'disabled' => $disabled,
                ])
                ->add('targets', CountryChoiceType::class, [
                    'label'    => 'ekyna_commerce.tax_rule.field.targets',
                    'enabled'  => false,
                    'multiple' => true,
                    'disabled' => $disabled,
                ])
                ->add('taxes', TaxChoiceType::class, [
                    'multiple'  => true,
                    'allow_new' => true,
                    'disabled'  => $disabled,
                ])
                ->add('mentions', MentionsType::class, [
                    'mention_class'     => Entity\TaxRuleMention::class,
                    'translation_class' => Entity\TaxRuleMentionTranslation::class,
                ]);
        });
    }
}
