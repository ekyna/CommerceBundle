<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MoneyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerStates;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CoreBundle\Form\Type\ColorPickerType;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\UserBundle\Form\Type\UserSearchType;
use Ekyna\Component\Commerce\Features;
use libphonenumber\PhoneNumberType as PhoneType;
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
     * @var Features
     */
    private $features;


    /**
     * CustomerType constructor.
     *
     * @param Features $features
     * @param string   $customerClass
     */
    public function __construct(Features $features, string $customerClass)
    {
        parent::__construct($customerClass);

        $this->features = $features;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', CustomerSearchType::class, [
                'label'    => 'ekyna_commerce.customer.field.parent',
                'required' => false,
            ])
            ->add('user', UserSearchType::class, [
                'required' => false,
            ])
            ->add('inCharge', UserChoiceType::class, [
                'label'    => 'ekyna_commerce.customer.field.in_charge',
                'required' => false,
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
                'label'       => 'ekyna_core.field.phone',
                'required'    => false,
                'number_attr' => [
                    'autocomplete' => 'tel-national',
                ],
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'       => 'ekyna_core.field.mobile',
                'required'    => false,
                'type'        => PhoneType::MOBILE,
                'number_attr' => [
                    'autocomplete' => 'tel-national',
                ],
            ])
            ->add('state', Type\ChoiceType::class, [
                'label'   => 'ekyna_core.field.status',
                'choices' => CustomerStates::getChoices(),
                'select2' => false,
            ])
            ->add('currency', CurrencyChoiceType::class)
            ->add('locale', LocaleChoiceType::class)
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ])
            ->add('tags', TagChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ]);

        if ($this->features->isEnabled(Features::CUSTOMER_GRAPHIC)) {
            $builder
                ->add('brandLogo', CustomerLogoType::class, [
                    'required' => false,
                ])
                ->add('brandColor', ColorPickerType::class, [
                    'label'    => 'ekyna_core.field.color',
                    'required' => false,
                ])
                ->add('brandUrl', Type\UrlType::class, [
                    'label'    => 'ekyna_core.field.url',
                    'required' => false,
                ])
                ->add('documentFooter', TinymceType::class, [
                    'label'    => 'ekyna_commerce.sale.field.document_footer',
                    'theme'    => 'simple',
                    'required' => false,
                ])
                ->add('documentTypes', ConstantChoiceType::class, [
                    'label'    => 'ekyna_commerce.customer.field.document_types',
                    'class'    => DocumentTypes::class,
                    'multiple' => true,
                    'required' => false,
                ]);
        }

        if ($this->features->isEnabled(Features::BIRTHDAY)) {
            $builder->add('birthday', Type\DateTimeType::class, [
                'label'    => 'ekyna_core.field.birthday',
                'required' => false,
                'format'   => 'dd/MM/yyyy', // TODO localized format
                'attr'     => [
                    'autocomplete' => 'bday',
                ],
            ]);
        }

        $formModifier = function (FormInterface $form, CustomerInterface $customer, $hasParent) {
            $form
                ->add('companyNumber', Type\TextType::class, [
                    'label'    => 'ekyna_commerce.customer.field.company_number',
                    'required' => false,
                    'disabled' => $hasParent,
                ])
                ->add('customerGroup', CustomerGroupChoiceType::class, [
                    'allow_new' => true,
                    'disabled'  => $hasParent,
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
                ->add('defaultPaymentMethod', PaymentMethodChoiceType::class, [
                    'label'       => 'ekyna_commerce.customer.field.default_payment_method',
                    'required'    => false,
                    'public'      => false,
                    'credit'      => false,
                    'outstanding' => false,
                    'disabled'    => $hasParent,
                    'attr'        => [
                        'help_text' => 'ekyna_commerce.customer.help.default_payment_method',
                    ],
                ])
                ->add('paymentMethods', PaymentMethodChoiceType::class, [
                    'label'       => 'ekyna_commerce.customer.field.payment_methods',
                    'required'    => false,
                    'multiple'    => true,
                    'public'      => false,
                    'credit'      => false,
                    'outstanding' => false,
                    'disabled'    => $hasParent,
                    'attr'        => [
                        'help_text' => 'ekyna_commerce.customer.help.payment_methods',
                    ],
                ])
                ->add('outstandingLimit', MoneyType::class, [
                    'label'    => 'ekyna_commerce.sale.field.outstanding_limit',
                    'quote'    => $customer->getCurrency(),
                    'disabled' => $hasParent,
                ])
                ->add('outstandingOverflow', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.customer.field.outstanding_overflow',
                    'required' => false,
                    'disabled' => $hasParent,
                    'attr'     => [
                        'align_with_widget' => true,
                        'help_text'         => 'ekyna_commerce.customer.help.outstanding_overflow',
                    ],
                ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var CustomerInterface $customer */
            $customer = $event->getData();

            $formModifier($event->getForm(), $customer, $customer->hasParent());
        });

        $builder
            ->get('parent')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
                $customer = $event->getForm()->getParent()->getData();
                /** @var CustomerInterface $customer */
                $parent = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $customer, null !== $parent);
            });
    }
}
