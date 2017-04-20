<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class NotifyModelTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label'    => t('field.subject', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'help_text' => t('notify_model.help.subject', [], 'EkynaCommerce'),
                ],
            ])
            ->add('message', TinymceType::class, [
                'label'    => t('field.message', [], 'EkynaUi'),
                'theme'    => 'front',
                'required' => false,
            ]);
    }
}
