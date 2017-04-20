<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Accounting;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\AccountingTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AccountingType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Accounting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label' => t('field.number', [], 'EkynaUi'),
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('type', ConstantChoiceType::class, [
                'label' => t('field.type', [], 'EkynaUi'),
                'class' => AccountingTypes::class,
            ])
            ->add('taxRule', ResourceChoiceType::class, [
                'resource' => 'ekyna_commerce.tax_rule',
                'required' => false,
            ])
            ->add('tax', ResourceChoiceType::class, [
                'resource' => 'ekyna_commerce.tax',
                'required' => false,
            ])
            ->add('paymentMethod', PaymentMethodChoiceType::class, [
                'public'      => false,
                'outstanding' => false,
                'required'    => false,
            ])
            ->add('customerGroups', CustomerGroupChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ]);
    }
}
