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
                'label'    => 'ekyna_core.field.from_date',
                'required' => false,
            ])
            ->add('to', Type\DateType::class, [
                'label'    => 'ekyna_core.field.to_date',
                'required' => false,
            ])
            ->add('byOrder', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.customer.balance.by_order',
                'required' => false,
            ])
            ->add('notDone', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.customer.balance.not_done',
                'required' => false,
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
