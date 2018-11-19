<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerStates;
use Ekyna\Bundle\UserBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\UserBundle\Form\Type\UserSearchType;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

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
            ->add('parent', CustomerSearchType::class, [
                'label'    => 'ekyna_commerce.customer.field.parent',
                'required' => false,
            ])
            ->add('user', UserSearchType::class, [
                'required'  => false,
                'roles'     => ['ROLE_USER', 'ROLE_ADMIN'],
                'add_route' => 'ekyna_user_user_admin_new',
            ])
            ->add('inCharge', UserChoiceType::class, [
                'label'    => 'ekyna_commerce.customer.field.in_charge',
                'required' => false,
                'roles'    => ['ROLE_ADMIN'],
            ])
            ->add('email', Type\EmailType::class, [
                'label' => 'ekyna_core.field.email',
                'attr'  => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
                'attr'     => [
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('identity', IdentityType::class)
            ->add('phone', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.phone',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
                'attr'           => [
                    'autocomplete' => 'tel-national',
                ],
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'          => 'ekyna_core.field.mobile',
                'required'       => false,
                'default_region' => 'FR', // TODO get user locale
                'format'         => PhoneNumberFormat::NATIONAL,
                'attr'           => [
                    'autocomplete' => 'tel-national',
                ],
            ])
            ->add('state', Type\ChoiceType::class, [
                'label'   => 'ekyna_core.field.status',
                'choices' => CustomerStates::getChoices(),
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ]);

        $formModifier = function (FormInterface $form, $hasParent) {
            $form
                ->add('customerGroup', ResourceType::class, [
                    'label'        => 'ekyna_commerce.customer_group.label.plural',
                    'class'        => $this->customerGroupClass,
                    'allow_new'    => true,
                    'choice_label' => 'name',
                    'disabled'     => $hasParent,
                ])
                ->add('vatNumber', VatNumberType::class, [
                    'disabled' => $hasParent,
                ])
                ->add('vatValid', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.pricing.field.vat_valid',
                    'required' => false,
                    'disabled' => $hasParent,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('paymentTerm', PaymentTermChoiceType::class, [
                    'disabled' => $hasParent,
                ])
                ->add('outstandingLimit', Type\NumberType::class, [
                    'label'    => 'ekyna_commerce.sale.field.outstanding_limit',
                    'scale'    => 2,
                    'disabled' => $hasParent,
                ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
            $customer = $event->getData();

            $formModifier($event->getForm(), $customer->hasParent());
        });

        $builder
            ->get('parent')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
                /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
                $parent = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), null !== $parent);
            });
    }
}
