<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.designation',
                'sizing' => 'sm',
            ])
            ->add('reference', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.reference',
                'sizing' => 'sm',
            ])
            ->add('quantity', Symfony\NumberType::class, [
                'label' => 'ekyna_core.field.quantity',
                'sizing' => 'sm',
                // TODO 'scale' => 2, // from packaging mode
            ])
            ->add('netPrice', Symfony\NumberType::class, [
                'label' => 'ekyna_core.field.designation',
                'sizing' => 'sm',
                // TODO 'scale' => 2, // currency option from supplier order
            ])
            /* TODO ->add('subject', Symfony\HiddenType::class)*/;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SupplierOrderItemInterface::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_order_item';
    }
}
