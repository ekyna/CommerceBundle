<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\HiddenEntityType;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $supplierProductClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $supplierProductClass
     */
    public function __construct($dataClass, $supplierProductClass)
    {
        parent::__construct($dataClass);

        $this->supplierProductClass = $supplierProductClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.designation',
                'attr'  => [
                    'class' => 'order-item-designation',
                ],
                'error_bubbling' => true,
            ])
            ->add('reference', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.reference',
                'attr'  => [
                    'class' => 'order-item-reference',
                ],
                'error_bubbling' => true,
            ])
            ->add('netPrice', Symfony\NumberType::class, [
                'label' => 'ekyna_core.field.designation',
                'attr'  => [
                    'class' => 'order-item-net-price',
                ],
                // TODO 'scale' => 2, // currency option from supplier order
                'error_bubbling' => true,
            ])
            ->add('quantity', Symfony\NumberType::class, [
                'label' => 'ekyna_core.field.quantity',
                'attr'  => [
                    'class' => 'order-item-quantity',
                ],
                // TODO 'scale' => 2, // from packaging mode
                'error_bubbling' => true,
            ])
            ->add('product', HiddenEntityType::class, [
                'class' => $this->supplierProductClass,
                'attr'  => [
                    'class' => 'order-item-product',
                ],
                'error_bubbling' => true,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'error_bubbling' => false,
            'attr' => [
                'class' => 'commerce-supplier-order-item',
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_order_item';
    }
}
