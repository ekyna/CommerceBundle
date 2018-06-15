<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class NotifyModelTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelTranslationType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label'    => 'ekyna_core.field.subject',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.notify_model.help.subject',
                ],
            ])
            ->add('message', TinymceType::class, [
                'label'    => 'ekyna_core.field.message',
                'theme'    => 'front',
                'required' => false,
            ]);
    }
}
