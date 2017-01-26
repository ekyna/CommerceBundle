<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdjustmentsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'          => false,
                'prototype_name' => '__adjustment__',
                'entry_options'  => function (Options $options) {
                    return [
                        'label' => false,
                        'types' => $options['types'],
                        'modes' => $options['modes'],
                    ];
                },
                'allow_add'      => true,
                'allow_delete'   => true,
                'allow_sort'     => true,
                'attr'           => ['widget_col' => 12],
                'types'          => [],
                'modes'          => [],
            ])
            ->setAllowedTypes('types', 'array')
            ->setAllowedTypes('modes', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_adjustments';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
