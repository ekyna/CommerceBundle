<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Support;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class TicketType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Support
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['admin_mode']) {
            $builder
                ->add('inCharge', UserChoiceType::class, [
                    'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                    'required' => false,
                ])
                ->add('customer', CustomerSearchType::class, [
                    'required' => false,
                ])
                ->add('orders', ResourceSearchType::class, [
                    'resource' => 'ekyna_commerce.order',
                    'multiple' => true,
                    'required' => false,
                ])
                ->add('quotes', ResourceSearchType::class, [
                    'resource' => 'ekyna_commerce.quote',
                    'multiple' => true,
                    'required' => false,
                ])
                ->add('internal', CheckboxType::class, [
                    'label'    => t('field.internal', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
        }

        $builder
            ->add('subject', TextType::class, [
                'label' => t('field.subject', [], 'EkynaUi'),
            ])
            ->add('message', TicketMessageType::class, [
                'property_path'   => 'messages[0]',
                'auto_initialize' => false,
            ]);
    }
}
