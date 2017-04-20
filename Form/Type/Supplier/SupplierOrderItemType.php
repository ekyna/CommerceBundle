<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\HiddenResourceType;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierOrderItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemType extends AbstractResourceType
{
    protected string $supplierProductClass;

    public function __construct(string $supplierProductClass)
    {
        $this->supplierProductClass = $supplierProductClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', Symfony\TextType::class, [
                'label'          => t('field.designation', [], 'EkynaUi'),
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-designation',
                ],
            ])
            ->add('reference', Symfony\TextType::class, [
                'label'          => t('field.reference', [], 'EkynaUi'),
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-reference',
                ],
            ])
            ->add('netPrice', Symfony\MoneyType::class, [
                'label'          => t('field.designation', [], 'EkynaUi'),
                'decimal'        => true,
                'currency'       => $options['currency'],
                'scale'          => 5,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-net-price',
                ],
            ])
            ->add('weight', Symfony\NumberType::class, [
                'label'   => t('field.weight', [], 'EkynaUi'),
                'decimal' => true,
                'scale'   => 3,
                'attr'    => [
                    'class'       => 'order-item-weight',
                    'input_group' => ['append' => 'Kg'],
                ],
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'attr' => [
                    'class' => 'order-item-tax-group',
                ],
            ])
            ->add('quantity', Symfony\NumberType::class, [
                'label'          => t('field.quantity', [], 'EkynaUi'),
                'decimal'        => true,
                'scale'          => 3, // TODO Packaging format
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-quantity',
                ],
            ])
            ->add('product', HiddenResourceType::class, [
                'class'          => $this->supplierProductClass,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'order-item-product',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_supplier_order_item';
    }
}
