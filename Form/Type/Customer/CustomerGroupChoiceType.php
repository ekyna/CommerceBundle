<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerGroupChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $customerGroupClass;


    /**
     * Constructor.
     *
     * @param string $customerGroupClass
     */
    public function __construct($customerGroupClass)
    {
        $this->customerGroupClass = $customerGroupClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'             => 'ekyna_commerce.customer_group.label.singular',
            'class'             => $this->customerGroupClass,
            'preferred_choices' => function (CustomerGroupInterface $customerGroup) {
                return $customerGroup->isDefault();
            },
            'choice_attr' => function($value) {
                if ($value instanceof CustomerGroupInterface) {
                    return [
                        'data-business' => $value->isBusiness() ? '1' : '0',
                    ];
                }

                return [];
            }
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
