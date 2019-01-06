<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Support;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TicketMessageType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Support
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', TinymceType::class, [
            'label' => 'ekyna_core.field.content',
            'theme' => $options['admin_mode'] ? 'advanced' : 'front',
        ]);

        /*if ($options['admin_mode']) {
            $builder->add('notify', CheckboxType::class, [
                'label'    => 'ekyna_core.button.notify',
                'required' => false,
                'attr'     => [
                    'align_with_widget',
                ],
            ]);
        }*/
    }
}
