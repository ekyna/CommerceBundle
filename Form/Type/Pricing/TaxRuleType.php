<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionsType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Pricing\Entity;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class TaxRuleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var TaxRuleInterface $rule */
            $rule = $event->getData();
            $form = $event->getForm();

            $disabled = !empty($rule->getCode());

            $form
                ->add('name', Type\TextType::class, [
                    'label'    => t('field.name', [], 'EkynaUi'),
                    'disabled' => $disabled,
                ])
                ->add('priority', Type\IntegerType::class, [
                    'label'    => t('field.priority', [], 'EkynaUi'),
                    'disabled' => $disabled,
                ])
                ->add('customer', Type\CheckboxType::class, [
                    'label'    => t('tax_rule.field.customer', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $disabled,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('business', Type\CheckboxType::class, [
                    'label'    => t('tax_rule.field.business', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $disabled,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('sources', CountryChoiceType::class, [
                    'label'    => t('tax_rule.field.sources', [], 'EkynaCommerce'),
                    'enabled'  => false,
                    'multiple' => true,
                    'disabled' => $disabled,
                ])
                ->add('targets', CountryChoiceType::class, [
                    'label'    => t('tax_rule.field.targets', [], 'EkynaCommerce'),
                    'enabled'  => false,
                    'multiple' => true,
                    'disabled' => $disabled,
                ])
                ->add('taxes', ResourceChoiceType::class, [
                    'resource'  => 'ekyna_commerce.tax',
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
