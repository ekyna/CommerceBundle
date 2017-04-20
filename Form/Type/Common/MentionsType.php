<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class MentionsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MentionsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'mention_class',
                'translation_class',
            ])
            ->setDefaults([
                'label'         => t('mention.label.plural', [], 'EkynaCommerce'),
                'entry_type'    => MentionType::class,
                'entry_options' => function (Options $options, $value) {
                    if (!is_array($value)) {
                        $value = [];
                    }

                    $value['data_class'] = $options['mention_class'];
                    $value['translation_class'] = $options['translation_class'];

                    return $value;
                },
                'allow_add'     => true,
                'allow_delete'  => true,
                'allow_sort'    => true,
                'required'      => false,
            ])
            ->setAllowedTypes('mention_class', 'string')
            ->setAllowedTypes('translation_class', 'string');
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
