<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\UserBundle\Form\Type\IdentityType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends ResourceFormType
{
    /**
     * @var string
     */
    private $customerClass;


    /**
     * Constructor.
     *
     * @param string $orderClass
     * @param string $customerClass
     */
    public function __construct($orderClass, $customerClass)
    {
        parent::__construct($orderClass);

        $this->customerClass = $customerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'disabled' => true,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'sizing' => 'sm',
            ])
            // TODO customer search
            ->add('customer', ResourceType::class, [
                'label'     => 'ekyna_commerce.customer.label.singular',
                'class'     => $this->customerClass,
                'allow_new' => true,
                'required'  => false,
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
            ])
            ->add('identity', IdentityType::class)
            ->add('email', Type\EmailType::class, [
                'label'    => 'ekyna_core.field.email',
                'required' => false,
            ])
            ->add('invoiceAddress', OrderAddressType::class, [
                'label' => 'ekyna_commerce.order.field.invoice_address',
            ])
            ->add('sameAddress', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.order.field.same_address',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('deliveryAddress', OrderAddressType::class, [
                'label'    => 'ekyna_commerce.order.field.delivery_address',
                'required' => false,
            ])
            ->add('items', OrderItemsType::class)
            ->add('adjustments', AdjustmentsType::class, [
                'entry_type'            => OrderAdjustmentType::class,
                'add_button_text'       => 'ekyna_commerce.order.form.add_adjustment',
                'delete_button_confirm' => 'ekyna_commerce.order.form.remove_adjustment',
            ]);
    }
}
