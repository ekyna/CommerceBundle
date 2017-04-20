<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SaleItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'          => false,
                'entry_options'  => ['label' => false],
                'allow_add'      => true,
                'allow_delete'   => true,
                'allow_sort'     => true,
                'attr'           => ['widget_col' => 12],
                'children_mode'  => false,
            ])
            ->setDefault('prototype_name', function (Options $options) {
                if ($options['children_mode']) {
                    return '__child_item__';
                }

                return '__item__';
            })
            ->setDefault('add_button_text', function (Options $options) {
                if ($options['children_mode']) {
                    return t('sale.form.add_child_item', [], 'EkynaCommerce');
                }

                return t('sale.form.add_item', [], 'EkynaCommerce');
            })
            ->setDefault('delete_button_confirm', function (Options $options) {
                if ($options['children_mode']) {
                    return t('sale.form.remove_child_item', [], 'EkynaCommerce');
                }

                return t('sale.form.remove_item', [], 'EkynaCommerce');
            })
            ->setAllowedTypes('children_mode', 'bool');
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['children_mode'] = $options['children_mode'];
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_sale_items';
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
