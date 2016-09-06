<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
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
            ->add('customer', EntitySearchType::class, [
                'label'           => 'ekyna_commerce.customer.label.singular',
                'class'          => $this->customerClass,
                'search_route'    => 'ekyna_commerce_customer_admin_search',
                'find_route'      => 'ekyna_commerce_customer_admin_find',
                'allow_clear'     => false,
                'choice_label' => function(CustomerInterface $data) {
                    $output = $data->getFirstName() . ' ' . $data->getLastName() . ' &lt;<em>' . $data->getEmail() . '</em>&gt;';
                    if (0 < strlen($data->getCompany())) {
                        $output = '[<strong>' . $data->getCompany() . '</strong>] ' . $output;
                    }
                    return '<span>' . $output . '</span>';
                },
                'format_function' =>
                    "if(!data.id)return 'Rechercher';" .
                    "var output=data.first_name+' '+data.last_name+' &lt;<em>'+data.email+'</em>&gt;';" .
                    "if(data.company)output='[<strong>'+data.company+'</strong>] '+output;" .
                    "return $('<span>'+output+'</span>');",
                'required'        => false,
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
            /*->add('items', OrderItemsType::class)
            ->add('adjustments', AdjustmentsType::class, [
                'entry_type'            => OrderAdjustmentType::class,
                'add_button_text'       => 'ekyna_commerce.order.form.add_adjustment',
                'delete_button_confirm' => 'ekyna_commerce.order.form.remove_adjustment',
            ])*/;
    }
}
