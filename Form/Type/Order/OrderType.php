<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends SaleType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        string                                         $defaultCurrency
    ) {
        parent::__construct($defaultCurrency);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('initiatorCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.initiator_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('originCustomer', CustomerSearchType::class, [
                'label'    => t('sale.field.origin_customer', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('tags', TagChoiceType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var OrderInterface $order */
            $order = $event->getData();
            $form = $event->getForm();

            $form
                ->add('sample', CheckboxType::class, [
                    'label'    => t('field.sample', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $order && ($order->hasPayments() || $order->hasInvoices() || $order->isReleased()),
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('inCharge', UserChoiceType::class, [
                    'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                    'roles'    => [],
                    'required' => false,
                    'disabled' => $order->getInCharge() && !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN'),
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', OrderAddressType::class);
    }
}
