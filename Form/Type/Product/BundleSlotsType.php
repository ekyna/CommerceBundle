<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleSlotsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'configurable'    => false,
                'label'           => 'ekyna_commerce.bundle_slot.label.plural',
                'prototype_name'  => '__slot__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => true,
                'add_button_text' => function (Options $options) {
                    if ($options['configurable']) {
                        return 'ekyna_commerce.bundle_slot.button.add_configurable';
                    }

                    return 'ekyna_commerce.bundle_slot.button.add';
                },
                'entry_type'      => BundleSlotType::class,
                'entry_options'   => function (Options $options) {
                    return [
                        'configurable' => $options['configurable'],
                    ];
                },
            ])
            ->setAllowedTypes('configurable', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
