<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Accounting;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxRuleChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\AccountingTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AccountingType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Accounting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label' => 'ekyna_core.field.number',
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('type', Type\ChoiceType::class, [
                'label'   => 'ekyna_core.field.type',
                'choices' => AccountingTypes::getChoices(),
            ])
            /*->add('name', Type\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'required' => false,
            ])*/
            ->add('taxRule', TaxRuleChoiceType::class, [
                'required' => false,
            ])
            ->add('tax', TaxChoiceType::class, [
                'required' => false,
            ])
            ->add('paymentMethod', PaymentMethodChoiceType::class, [
                'outstanding' => false,
                'required'    => false,
            ])
            ->add('customerGroups', CustomerGroupChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ]);
    }
}
