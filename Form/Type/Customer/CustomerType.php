<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends ResourceFormType
{
    /**
     * @var string
     */
    private $customerGroupClass;

    /**
     * @var string
     */
    private $userClass;


    /**
     * Constructor.
     *
     * @param string $customerClass
     * @param string $customerGroupClass
     * @param string $userClass
     */
    public function __construct($customerClass, $customerGroupClass, $userClass)
    {
        parent::__construct($customerClass);

        $this->customerGroupClass = $customerGroupClass;
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customerGroup', ResourceType::class, [
                'label'        => 'ekyna_commerce.customer_group.label.plural',
                'class'        => $this->customerGroupClass,
                'allow_new'    => true,
                'choice_label' => 'name',
            ])
            ->add('parent', ResourceType::class, [
                'label'     => 'ekyna_commerce.customer.field.parent',
                'class'     => $this->dataClass,
                'allow_new' => true,
                'required'  => false,
            ])
            ->add('user', ResourceType::class, [
                'label'     => 'ekyna_user.user.label.singular',
                'allow_new' => true,
                'class'     => $this->userClass,
                'required'  => false,
            ])
            ->add('email', Type\EmailType::class, [
                'label' => 'ekyna_core.field.email',
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
            ])
            ->add('identity', IdentityType::class)
            ->add('phone', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.phone',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.mobile',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
            ])
            ->add('paymentTerm', PaymentTermChoiceType::class)
            ->add('outstandingLimit', Type\NumberType::class, [
                'label' => 'ekyna_commerce.customer.field.outstanding_limit',
                'scale' => 2,
            ]);
    }
}
