<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\HiddenEntityType;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
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
                'label'          => 'ekyna_core.field.designation',
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-designation',
                ],
            ])
            ->add('reference', Symfony\TextType::class, [
                'label'          => 'ekyna_core.field.reference',
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-reference',
                ],
            ])
            ->add('netPrice', MoneyType::class, [
                'label'          => 'ekyna_core.field.designation',
                'currency'       => $options['currency'],
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-net-price',
                ],
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'attr' => [
                    'class' => 'order-item-tax-group',
                ],
            ])
            ->add('quantity', Symfony\NumberType::class, [
                'label'          => 'ekyna_core.field.quantity',
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-quantity',
                ],
                // TODO 'scale' => 2, // from packaging mode
            ])
            ->add('product', HiddenEntityType::class, [
                'class'          => $this->supplierProductClass,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-product',
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'error_bubbling' => false,
                'currency'       => null,
                'attr'           => [
                    'class' => 'commerce-supplier-order-item',
                ],
            ])
            ->setAllowedTypes('currency', 'string');
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_order_item';
    }
}
