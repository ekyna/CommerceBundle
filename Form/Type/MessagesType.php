<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessagesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MessagesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'message_class',
                'translation_class',
            ])
            ->setDefaults([
                'label'         => 'ekyna_payment.message.label.plural',
                'entry_type'    => MessageType::class,
                'entry_options' => function (Options $options, $value) {
                    if (!is_array($value)) {
                        $value = [];
                    }

                    $value['data_class'] = $options['message_class'];
                    $value['translation_class'] = $options['translation_class'];

                    return $value;
                },
            ])
            ->setAllowedTypes('message_class', 'string')
            ->setAllowedTypes('translation_class', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_messages';
    }
}
