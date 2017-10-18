<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressType extends ResourceFormType
{
    /**
     * @var string
     */
    private $customerClass;


    /**
     * Constructor.
     *
     * @param string $addressClass
     * @param string $customerClass
     */
    public function __construct($addressClass, $customerClass)
    {
        parent::__construct($addressClass);

        $this->customerClass = $customerClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['defaults']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
                $address = $event->getData();
                $form = $event->getForm();

                $form
                    ->add('invoiceDefault', CheckboxType::class, [
                        'label'    => 'ekyna_commerce.customer_address.field.invoice_default',
                        'disabled' => $address->isInvoiceDefault(),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ])
                    ->add('deliveryDefault', CheckboxType::class, [
                        'label'    => 'ekyna_commerce.customer_address.field.delivery_default',
                        'disabled' => $address->isDeliveryDefault(),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
            });
        }

        if ($options['customer_form']) {
            $builder->add('customer', ResourceType::class, [
                'label'     => 'ekyna_commerce.customer.label.singular',
                'class'     => $this->customerClass,
                'allow_new' => true,
                'select2'   => $options['select2'],
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer_form', false)
            ->setDefault('defaults', true)
            ->setAllowedTypes('customer_form', 'bool')
            ->setAllowedTypes('defaults', 'bool');
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
