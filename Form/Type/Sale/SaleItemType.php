<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\EventListener\SaleItemTypeSubscriber;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AdjustmentsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['with_collections']) {
            $builder->add('items', SaleItemsType::class, [
                'property_path' => 'children',
                'children_mode' => true,
                'entry_type'    => $options['item_type'],
                'entry_options' => [
                    'label'            => false,
                    'with_collections' => false,
                ],
            ]);
        }

        $builder->add('adjustments', AdjustmentsType::class, [
            'prototype_name'        => '__item_adjustment__',
            'entry_type'            => $options['item_adjustment_type'],
            'add_button_text'       => 'ekyna_commerce.sale.form.add_item_adjustment',
            'delete_button_confirm' => 'ekyna_commerce.sale.form.remove_item_adjustment',
        ]);

        $subscriber = new SaleItemTypeSubscriber($this->getFields($options));
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['with_collections'] = $options['with_collections'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'with_collections'     => true,
                'item_type'            => null,
                'item_adjustment_type' => null,
            ])
            ->setAllowedTypes('with_collections', 'bool')
            ->setAllowedTypes('item_type', 'string')
            ->setAllowedTypes('item_adjustment_type', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_sale_item';
    }

    /**
     * Returns the fields definitions.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getFields(array $options)
    {
        return [
            ['designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ]],
            ['reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
            ]],
            ['weight', Type\NumberType::class, [
                'label'    => 'ekyna_core.field.weight', // TODO unit weight ?
                'scale'    => 3,
                'required' => false,
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
            ]],
            ['netPrice', MoneyType::class, [
                'label'    => 'ekyna_commerce.sale.field.net_unit',
                //'scale'    => 5,
                'required' => false,
                'attr'     => [
                    'placeholder' => 'ekyna_commerce.sale.field.net_unit',
                    //'input_group' => ['append' => '€'],  // TODO sale currency
                ],
            ]],
            ['taxGroup', TaxGroupChoiceType::class, [
                'label'    => 'ekyna_commerce.sale_item.field.tax_group',
                'required' => false,
                'select2'  => false,
                'attr'     => [
                    'placeholder' => 'ekyna_commerce.sale_item.field.tax_group',
                ],
            ]],
            ['quantity', Type\IntegerType::class, [
                'label' => 'ekyna_core.field.quantity',
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.quantity',
                    'min'         => 1,
                ],
            ]],
            ['position', CollectionPositionType::class, []],
        ];
    }
}
