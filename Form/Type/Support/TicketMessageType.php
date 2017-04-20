<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Support;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class TicketMessageType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Support
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TinymceType::class, [
            'label' => t('field.content', [], 'EkynaUi'),
            'theme' => $options['admin_mode'] ? 'advanced' : 'front',
        ]);

        if ($options['admin_mode']) {
            $builder
                ->add('internal', CheckboxType::class, [
                    'label'    => t('field.internal', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('notify', CheckboxType::class, [
                    'label'    => t('button.notify', [], 'EkynaUi'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
        }
    }
}
