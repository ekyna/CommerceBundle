<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IncotermType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends SaleType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('destinationAddress', SaleAddressType::class, [
                'label'          => t('sale.field.destination_address', [], 'EkynaCommerce'),
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'mode'           => SaleAddressType::MODE_DESTINATION,
                'customer_field' => 'customer',
            ])
            ->add('autoInvoice', CheckboxType::class, [
                'label'    => t('sale.field.auto_invoice', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('project', ResourceSearchType::class, [
                'resource' => 'ekyna_commerce.project',
                'required' => false,
            ])
            ->add('initiatorCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.initiator_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('originCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.origin_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('inCharge', UserChoiceType::class, [
                'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                'roles'    => [],
                'required' => false,
            ])
            ->add('tags', TagChoiceType::class);

        FormUtil::bindFormEventsToChildren(
            $builder,
            [
                FormEvents::POST_SET_DATA => 0,
                FormEvents::PRE_SET_DATA  => 2048,
                FormEvents::PRE_SUBMIT    => 2048,
                FormEvents::POST_SUBMIT   => 2048,
            ],
            ['destinationAddress']
        );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var OrderInterface $order */
            $order = $event->getData();
            $form = $event->getForm();

            $form
                ->add('incoterm', IncotermType::class, [
                    'disabled' => $order && $order->getIncoterm() && ($order->hasInvoices() || $order->hasShipments()),
                ])
                ->add('sample', CheckboxType::class, [
                    'label'    => t('field.sample', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $order && ($order->hasPayments() || $order->hasInvoices() || $order->isReleased()),
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('support', CheckboxType::class, [
                    'label'    => t('field.support', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', OrderAddressType::class);
    }
}
