<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerSearchType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerSearchType extends AbstractType
{
    /**
     * @var string
     */
    private $customerClass;


    /**
     * Constructor.
     *
     * @param string $customerClass
     */
    public function __construct($customerClass)
    {
        $this->customerClass = $customerClass;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'    => 'ekyna_commerce.customer.label.singular',
            'class'    => $this->customerClass,
            'route'    => 'ekyna_commerce_customer_admin_search',
            'required' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntitySearchType::class;
    }
}
