<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Component\Commerce\Customer\Balance\Balance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BalanceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BalanceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', Type\DateType::class, [
                'label'          => 'ekyna_core.field.from_date',
                'required'       => false,
                'picker_options' => [
                    'widgetPositioning' => [
                        'horizontal' => 'left',
                    ],
                ],
            ])
            ->add('to', Type\DateType::class, [
                'label'    => 'ekyna_core.field.to_date',
                'required' => false,
            ])
            ->add('filter', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.button.filter',
                'required' => true,
                'select2'  => false,
                'choices'  => [
                    'ekyna_commerce.customer.balance.all'             => Balance::FILTER_ALL,
                    'ekyna_commerce.customer.balance.due_invoices'    => Balance::FILTER_DUE_INVOICES,
                    'ekyna_commerce.customer.balance.befall_invoices' => Balance::FILTER_BEFALL_INVOICES,
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Balance::class);
    }
}
