<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Features;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
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

use function Symfony\Component\Translation\t;

/**
 * Class RegistrationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;
    private Features              $features;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Features              $features
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->features = $features;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add($this->createCustomerForm($builder))
            ->add('applyGroup', CustomerGroupChoiceType::class, [
                'label'         => t('account.registration.field.apply_group', [], 'EkynaCommerce'),
                'expanded'      => true,
                'choice_label'  => 'title',
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    $qb = $er->createQueryBuilder('cg');

                    return $qb
                        ->andWhere($qb->expr()->eq('cg.registration', true))
                        ->orderBy('cg.id', 'ASC');
                },
                'help'          => t('account.registration.help.apply_group', [], 'EkynaCommerce'),
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
                'label'    => t('field.comment', [], 'EkynaUi'),
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
                            'label'        => t('button.save', [], 'EkynaUi'),
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                ],
            ]);

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $builder->add('newsletter', Type\CheckboxType::class, [
                'label'    => t('account.registration.field.newsletter', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var Registration $registration */
            if (null === $registration = $event->getData()) {
                throw new LogicException('Customer must be set at this point.');
            }

            if (null === $customer = $registration->getCustomer()) {
                throw new LogicException('Customer must be set at this point.');
            }
            if (null === $user = $customer->getUser()) {
                throw new LogicException('Customer\'s user must be set at this point.');
            }

            if ($user->getId()) {
                return;
            }

            $form = $event->getForm();
            $form
                ->get('customer')
                ->add('email', Type\RepeatedType::class, [
                    'type'            => Type\EmailType::class,
                    'property_path'   => 'user.email',
                    'required'        => true,
                    'first_options'   => [
                        'label' => t('field.email', [], 'EkynaUi'),
                        'attr'  => [
                            'autocomplete' => 'email',
                        ],
                    ],
                    'second_options'  => [
                        'label' => t('account.registration.field.email_confirm', [], 'EkynaCommerce'),
                    ],
                    'invalid_message' => t('account.email.mismatch', [], 'EkynaCommerce'),
                ])
                ->add('plainPassword', Type\RepeatedType::class, [
                    'type'            => Type\PasswordType::class,
                    'property_path'   => 'user.plainPassword',
                    'required'        => true,
                    'first_options'   => [
                        'label'        => t('field.password', [], 'EkynaUi'),
                        'always_empty' => false,
                    ],
                    'second_options'  => [
                        'label'        => t('account.registration.field.password_confirm', [], 'EkynaCommerce'),
                        'always_empty' => false,
                    ],
                    'invalid_message' => 'fos_user.password.mismatch',
                ]);
        });

        // Fix some data before validation
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
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
            $email = $user->getEmail();
            // Copy user email into customer email
            $customer->setEmail($email);
        }, 2048);
    }

    private function createCustomerForm(FormBuilderInterface $builder): FormBuilderInterface
    {
        $form = $builder->create('customer', null, [
            'data_class' => CustomerInterface::class,
            'compound'   => true,
            'required'   => true,
        ]);

        $form
            ->add('company', Type\TextType::class, [
                'label'    => t('account.registration.field.company', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('companyNumber', Type\TextType::class, [
                'label'    => t('customer.field.company_number', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('vatNumber', VatNumberType::class, [
                'required' => false,
            ])
            ->add('identity', IdentityType::class, [
                'required' => true,
            ])
            ->add('phone', PhoneNumberType::class, [
                'label'       => t('field.phone', [], 'EkynaUi'),
                'required'    => false,
                'number_attr' => [
                    'autocomplete' => 'tel-national',
                ],
            ])
            ->add('mobile', PhoneNumberType::class, [
                'label'       => t('field.mobile', [], 'EkynaUi'),
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
                'label'    => t('field.birthday', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'autocomplete' => 'bday',
                ],
            ]);
        }

        return $form;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Registration::class,
            'csrf_token_id'     => 'registration',
            'validation_groups' => ['Registration'],
        ]);
    }
}
