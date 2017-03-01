<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
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
            'label'           => 'ekyna_commerce.customer.label.singular',
            'class'           => $this->customerClass,
            'required'        => false,
            'search_route'    => 'ekyna_commerce_customer_admin_search',
            'find_route'      => 'ekyna_commerce_customer_admin_find',
            //'allow_clear'     => false,
            'choice_label'    => function (CustomerInterface $data) {
                $output = $data->getFirstName() . ' ' . $data->getLastName() . ' &lt;' . $data->getEmail() . '&gt;';
                if (0 < strlen($data->getCompany())) {
                    $output = '[' . $data->getCompany() . '] ' . $output;
                }

                return $output;
            },
            'format_function' =>
                "if(!data.id)return 'Rechercher';" .
                "var output=data.first_name+' '+data.last_name+' &lt;<em>'+data.email+'</em>&gt;';" .
                "if(data.company)output='[<strong>'+data.company+'</strong>] '+output;" .
                "return $('<span>'+output+'</span>');",
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
