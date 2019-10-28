<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Support;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteSearchType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TicketType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Support
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['admin_mode']) {
            $builder
                ->add('inCharge', UserChoiceType::class, [
                    'label'    => 'ekyna_commerce.customer.field.in_charge',
                    'required' => false,
                ])
                ->add('customer', CustomerSearchType::class, [
                    'required' => false,
                ])
                ->add('orders', OrderSearchType::class, [
                    'multiple' => true,
                    'required' => false,
                ])
                ->add('quotes', QuoteSearchType::class, [
                    'multiple' => true,
                    'required' => false,
                ])
                ->add('internal', CheckboxType::class, [
                    'label'    => 'ekyna_commerce.field.internal',
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
        }

        $builder
            ->add('subject', TextType::class, [
                'label' => 'ekyna_core.field.subject',
            ])
            ->add('message', TicketMessageType::class, [
                'property_path'   => 'messages[0]',
                'auto_initialize' => false,
                'admin_mode'      => $options['admin_mode'],
            ]);
    }
}
