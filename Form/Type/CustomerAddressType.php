<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
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
        parent::buildForm($builder, $options);

        if ($options['customer_form']) {
            $builder
                ->add('customer', ResourceType::class, [
                    'label'     => 'ekyna_commerce.customer.label.singular',
                    'class'     => $this->customerClass,
                    'allow_new' => true,
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
            ->setAllowedTypes('customer_form', 'bool');
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
