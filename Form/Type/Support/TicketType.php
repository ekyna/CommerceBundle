<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Support;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteSearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
                ]);
        }

        $builder
            ->add('subject', TextType::class, [
                'label' => 'ekyna_core.field.subject',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
                /** @var \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $ticket */
                $ticket = $event->getData();

                if (0 < $ticket->getMessages()->count()) {
                    return;
                }

                $field = $builder
                    ->create('message', TicketMessageType::class, [
                        'property_path'   => 'messages[0]',
                        'auto_initialize' => false,
                    ])
                    ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($ticket) {
                        /** @var \Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface $message */
                        $message = $event->getForm()->getData();
                        $message->setTicket($ticket);
                    }, 2048)
                    ->getForm();

                $event->getForm()->add($field);
            });
    }
}
