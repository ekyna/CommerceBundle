<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\EventListener\OrderItemTypeSubscriber;
use Ekyna\Bundle\CommerceBundle\Helper\SubjectHelperInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemType extends ResourceFormType
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;


    /**
     * Constructor.
     *
     * @param string                 $itemClass
     * @param SubjectHelperInterface $itemHelper
     */
    public function __construct($itemClass, SubjectHelperInterface $itemHelper)
    {
        parent::__construct($itemClass);

        $this->subjectHelper = $itemHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['with_collections']) {
            $builder
                ->add('items', OrderItemsType::class, [
                    'property_path' => 'children',
                    'children_mode' => true,
                    'entry_options' => [
                        'label'            => false,
                        'with_collections' => false,
                    ],
                ]);
        }

        $builder->add('adjustments', AdjustmentsType::class, [
            'prototype_name'        => '__item_adjustment__',
            'entry_type'            => OrderItemAdjustmentType::class,
            'add_button_text'       => 'ekyna_commerce.order.form.add_item_adjustment',
            'delete_button_confirm' => 'ekyna_commerce.order.form.remove_item_adjustment',
        ]);

        $subscriber = new OrderItemTypeSubscriber($this->subjectHelper, $this->getFields($options));
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
            ->setDefault('with_collections', true)
            ->setAllowedTypes('with_collections', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_order_item';
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
                'label'  => 'ekyna_core.field.designation',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ]],
            ['reference', Type\TextType::class, [
                'label'  => 'ekyna_core.field.reference',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
            ]],
            ['weight', Type\IntegerType::class, [
                'label'  => 'ekyna_core.field.weight',
                'sizing' => 'sm',
                'required' => false,
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'g'],
                    'min'         => 0,
                ],
            ]],
            ['netPrice', Type\NumberType::class, [
                'label'  => 'ekyna_commerce.sale.field.net_unit',
                'scale'  => 5,
                'sizing' => 'sm',
                'required' => false,
                'attr'   => [
                    'placeholder' => 'ekyna_commerce.sale.field.net_unit',
                    'input_group' => ['append' => '€'],  // TODO order currency
                ],
            ]],
            ['quantity', Type\IntegerType::class, [
                'label'  => 'ekyna_core.field.quantity',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.quantity',
                    'min'         => 1,
                ],
            ]],
            ['position', Type\HiddenType::class, [
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]],
        ];
    }
}
