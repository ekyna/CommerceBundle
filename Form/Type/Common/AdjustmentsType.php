<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdjustmentsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
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

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_adjustments';
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
