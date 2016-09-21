<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Ekyna\Bundle\CommerceBundle\Form\Type\Product\BundleChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleChoicesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoicesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'configurable'   => false,
                'label'          => false,
                'prototype_name'  => '__choice__',
                'sub_widget_col' => function (Options $options) {
                    return $options['configurable'] ? 11 : 12;
                },
                'button_col'     => function (Options $options) {
                    return $options['configurable'] ? 1 : 0;
                },
                'allow_add'      => function (Options $options) {
                    return $options['configurable'];
                },
                'add_button_text' => function (Options $options) {
                    return $options['configurable']
                        ? 'ekyna_commerce.bundle_choice.button.add'
                        : false;
                },
                'allow_sort'     => function (Options $options) {
                    return $options['configurable'];
                },
                'allow_delete'   => function (Options $options) {
                    return $options['configurable'];
                },
                'entry_type'     => BundleChoiceType::class,
                'entry_options'  => function (Options $options) {
                    return [
                        'configurable' => $options['configurable'],
                    ];
                },
            ])
            ->setRequired('choice_class')
            ->setAllowedTypes('configurable', 'bool')
            ->setAllowedTypes('choice_class', 'string');
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
