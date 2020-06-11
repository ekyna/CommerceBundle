<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MentionsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MentionsType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'mention_class',
                'translation_class',
            ])
            ->setDefaults([
                'label'         => 'ekyna_commerce.mention.label.plural',
                'entry_type'    => MentionType::class,
                'entry_options' => function (Options $options, $value) {
                    if (!is_array($value)) {
                        $value = [];
                    }

                    $value['data_class']        = $options['mention_class'];
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

    /**
     * @inheritDoc
     */
    /*public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (is_a($options['message_class'], ShipmentMessage::class)) {
            $view->vars['translation_type'] = 'shipment';
        } elseif (is_a($options['message_class'], PaymentMessage::class)) {
            $view->vars['translation_type'] = 'payment';
        } else {
            $view->vars['translation_type'] = 'unknown';
        }
    }*/

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
