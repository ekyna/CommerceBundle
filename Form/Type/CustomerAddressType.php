<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressType extends AbstractAddressType
{
    /**
     * @var string
     */
    private $customerClass;


    /**
     * Constructor.
     *
     * @param string $addressClass
     * @param string $countryClass
     * @param string $customerClass
     */
    public function __construct($addressClass, $countryClass, $customerClass)
    {
        parent::__construct($addressClass, $countryClass);

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
        $resolver
            ->setDefault('customer_form', false)
            ->setAllowedTypes('customer_form', 'bool');
    }
}
