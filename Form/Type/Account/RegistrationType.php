<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Features;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use libphonenumber\PhoneNumberType as PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class RegistrationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Features
     */
    private $features;

    /**
     * @var string
     */
    private $customerClass;


    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param Features              $features
     * @param string                $customerClass
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Features $features,
        string $customerClass
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->customerClass = $customerClass;
        $this->features = $features;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->createCustomerForm($builder))
            ->add('applyGroup', CustomerGroupChoiceType::class, [
                'label'         => 'ekyna_commerce.account.registration.field.apply_group',
                'expanded'      => true,
                'choice_label'  => 'title',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('cg');

                    return $qb
                        ->andWhere($qb->expr()->eq('cg.registration', true))
                        ->orderBy('cg.id', 'ASC');
                },
                'attr'          => [
                    'help_text' => 'ekyna_commerce.account.registration.help.apply_group',
                ],
            ])
            ->add('invoiceAddress', CustomerAddressType::class, [
                'label'      => false,
                'required'   => true,
                'identity'   => false,
                'company'    => false,
                'phones'     => false,
                'defaults'   => false,
                'select2'    => false,
                'coordinate' => false,
                'section'    => 'billing',
            ])
            ->add('comment', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.comment',
                'required' => false,
            ])
            ->add('captcha', EWZRecaptchaType::class, [
                'label'       => '&nbsp;',
                'required'    => false,
                'mapped'      => false,
                'constraints' => [new IsTrue()],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'save' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.save',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                ],
            ]);

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $builder->add('newsletter', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.account.registration.field.newsletter',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Registration $registration */
            if (null === $registration = $event->getData()) {
                throw new LogicException("Customer must be set at this point.");
            }

            if (null === $customer = $registration->getCustomer()) {
                throw new LogicException("Customer must be set at this point.");
            }
            if (null === $user = $customer->getUser()) {
                throw new LogicException("Customer's user must be set at this point.");
            }

            $form = $event->getForm();

            if (null === $user->getId()) {
                $form
                    ->get('customer')
                    ->add('email', Type\RepeatedType::class, [
                        'type'            => Type\EmailType::class,
                        'property_path'   => 'user.email',
                        'required'        => true,
                        'first_options'   => [
                            'label' => 'ekyna_core.field.email',
                            'attr'  => [
                                'autocomplete' => 'email',
                            ],
                        ],
                        'second_options'  => [
                            'label' => 'ekyna_commerce.account.registration.field.email_confirm',
                        ],
                        'invalid_message' => 'ekyna_commerce.account.email.mismatch',
                    ])
                    ->add('plainPassword', Type\RepeatedType::class, [
                        'type'            => Type\PasswordType::class,
                        'property_path'   => 'user.plainPassword',
                        'required'        => true,
                        'first_options'   => [
                            'label'        => 'ekyna_core.field.password',
                            'always_empty' => false,
                        ],
                        'second_options'  => [
                            'label'        => 'ekyna_commerce.account.registration.field.password_confirm',
                            'always_empty' => false,
                        ],
                        'invalid_message' => 'fos_user.password.mismatch',
                    ]);
            }
        });

        // Fix some data before validation
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Registration $registration */
            $registration = $event->getData();

            $customer = $registration->getCustomer();

            // Address
            if (null !== $address = $registration->getInvoiceAddress()) {
                $customer->addAddress($address);

                // Copy fields from customer to default address
                $address
                    ->setCompany($customer->getCompany())
                    ->setPhone($customer->getPhone())
                    ->setMobile($customer->getMobile())
                    ->setInvoiceDefault(true)
                    ->setDeliveryDefault(true);

                $invoiceContact = $registration->getInvoiceContact();

                if ($invoiceContact && !$invoiceContact->isIdentityEmpty()) {
                    $address
                        ->setGender($invoiceContact->getGender())
                        ->setFirstName($invoiceContact->getFirstName())
                        ->setLastName($invoiceContact->getLastName());
                } else {
                    $address
                        ->setGender($customer->getGender())
                        ->setFirstName($customer->getFirstName())
                        ->setLastName($customer->getLastName());
                }
            }

            // User
            $user = $customer->getUser();
            // Copy user email to username
            $email = $user->getEmail();
            $user->setUsername($email);
            // Copy user email into customer email
            $customer->setEmail($email);
        }, 2048);
    }

    /**
     * Creates the customer form.
     *
     * @param FormBuilderInterface $builder
     *
     * @return FormBuilderInterface
     */
    private function createCustomerForm(FormBuilderInterface $builder)
    {
        $form = $builder->create('customer', null, [
            'data_class' => CustomerInterface::class,
            'compound'   => true,
            'required'   => true,
        ]);

        $form
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_commerce.account.registration.field.company',
                'required' => false,
                'attr'     => [
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('companyNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.customer.field.company_number',
                'required' => false,
            ])
            ->add('vatNumber', VatNumberType::class, [
                'required' => false,
            ])
            ->add('identity', IdentityType::class, [
                'required' => true,
            ])
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
            ->add('currency', CurrencyChoiceType::class)
            ->add('locale', LocaleChoiceType::class);

        if ($this->features->isEnabled(Features::BIRTHDAY)) {
            $form->add('birthday', Type\DateTimeType::class, [
                'label'    => 'ekyna_core.field.birthday',
                'required' => false,
                'format'   => 'dd/MM/yyyy', // TODO localized format
                'attr'     => [
                    'autocomplete' => 'bday',
                ],
            ]);
        }

        return $form;
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['user'] = null;
        $view->vars['user_owner'] = null;

        /** @var Registration $registration */
        $registration = $form->getData();
        $customer = $registration->getCustomer();

        if (null === $user = $customer->getUser()) {
            return;
        }

        $view->vars['user'] = $user;

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if ($token instanceof OAuthToken) {
            $view->vars['user_owner'] = $token->getResourceOwnerName();
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => Registration::class,
            'csrf_token_id'     => 'registration',
            'validation_groups' => ['Registration'],
        ]);
    }
}
