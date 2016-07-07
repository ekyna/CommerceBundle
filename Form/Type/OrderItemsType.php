<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderItemsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'                 => false,
                'entry_type'            => OrderItemType::class,
                'entry_options'         => ['label' => false],
                'allow_add'             => true,
                'allow_delete'          => true,
                'allow_sort'            => true,
                'attr'                  => ['widget_col' => 12],
                'children_mode'         => false,
            ])
            ->setDefault('add_button_text', function (Options $options) {
                if ($options['children_mode']) {
                    return 'ekyna_commerce.order.form.add_child_item';
                }

                return 'ekyna_commerce.order.form.add_item';
            })
            ->setDefault('delete_button_confirm', function (Options $options) {
                if ($options['children_mode']) {
                    return 'ekyna_commerce.order.form.remove_child_item';
                }

                return 'ekyna_commerce.order.form.remove_item';
            })
            ->setAllowedTypes('children_mode', 'bool');
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['children_mode'] = $options['children_mode'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_order_items';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
