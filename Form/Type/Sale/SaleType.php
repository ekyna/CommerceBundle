<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'required' => false,
                'disabled' => true,
            ])
            ->add('currency', CurrencyChoiceType::class)
            /*->add('state', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'choices'  => PaymentStates::getChoices(),
                'disabled' => $lockedMethod,
            ])*/
            ->add('customer', CustomerSearchType::class, [
                'required' => false,
            ])
            ->add('customerGroup', CustomerGroupChoiceType::class, [
                'required' => false,
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
            ])
            ->add('identity', IdentityType::class, [
                'required' => false,
            ])
            ->add('email', Type\EmailType::class, [
                'label'    => 'ekyna_core.field.email',
                'required' => false,
            ])
            ->add('invoiceAddress', SaleAddressType::class, [
                'label'          => 'ekyna_commerce.sale.field.invoice_address',
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'customer_field' => 'customer',
            ])
            ->add('deliveryAddress', SaleAddressType::class, [
                'label'          => 'ekyna_commerce.sale.field.delivery_address',
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'delivery'       => true,
                'customer_field' => 'customer',
            ])
            ->add('preferredShipmentMethod', ShipmentMethodChoiceType::class)
            ->add('paymentTerm', PaymentTermChoiceType::class, [
                'disabled' => true,
            ])
            ->add('voucherNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'required' => false,
            ])
            ->add('originNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ]);

        FormUtil::bindFormEventsToChildren(
            $builder,
            [
                FormEvents::PRE_SET_DATA => 2048,
                FormEvents::PRE_SUBMIT   => 2048,
                FormEvents::POST_SUBMIT  => 2048,
            ],
            ['invoiceAddress', 'deliveryAddress']
        );
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['address_type'])
            ->setAllowedTypes('address_type', 'string'); // TODO validation
    }
}
