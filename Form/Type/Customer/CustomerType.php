<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Customer;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerStates;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BNotifications;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\ColorPickerType;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Ekyna\Bundle\UserBundle\Form\Type\UserSearchType;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CNotifications;
use Ekyna\Component\Commerce\Features;
use libphonenumber\PhoneNumberType as PhoneType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends AbstractResourceType
{
    private Features $features;


    public function __construct(Features $features)
    {
        $this->features = $features;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parent', CustomerSearchType::class, [
                'label'    => t('customer.field.parent', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('user', UserSearchType::class, [
                'required' => false,
            ])
            ->add('inCharge', UserChoiceType::class, [
                'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                'roles'    => [],
                'required' => false,
            ])
            ->add('email', Type\EmailType::class, [
                'label' => t('field.email', [], 'EkynaUi'),
                'attr'  => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('company', Type\TextType::class, [
                'label'    => t('field.company', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('identity', IdentityType::class)
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
            ->add('customerPosition', ResourceChoiceType::class, [
                'resource'  => 'ekyna_commerce.customer_position',
                'required'  => false,
                'allow_new' => true,
            ])
            ->add('state', ConstantChoiceType::class, [
                'label'   => t('field.status', [], 'EkynaUi'),
                'class'   => CustomerStates::class,
                'select2' => false,
            ])
            ->add('currency', CurrencyChoiceType::class)
            ->add('locale', LocaleChoiceType::class)
            ->add('notifications', ConstantChoiceType::class, [
                'label'    => t('notification.label.plural', [], 'EkynaCommerce'),
                'class'    => BNotifications::class,
                'filter'   => [CNotifications::MANUAL],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => t('field.description', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('tags', TagChoiceType::class);

        if ($this->features->isEnabled(Features::CUSTOMER_GRAPHIC)) {
            $builder
                ->add('brandLogo', CustomerLogoType::class, [
                    'required' => false,
                ])
                ->add('brandColor', ColorPickerType::class, [
                    'label'    => t('field.color', [], 'EkynaUi'),
                    'required' => false,
                ])
                ->add('brandUrl', Type\UrlType::class, [
                    'label'    => t('field.url', [], 'EkynaUi'),
                    'required' => false,
                ])
                ->add('documentFooter', TinymceType::class, [
                    'label'    => t('sale.field.document_footer', [], 'EkynaCommerce'),
                    'theme'    => 'simple',
                    'required' => false,
                ])
                ->add('documentTypes', ConstantChoiceType::class, [
                    'label'    => t('customer.field.document_types', [], 'EkynaCommerce'),
                    'class'    => DocumentTypes::class,
                    'multiple' => true,
                    'required' => false,
                ]);
        }

        if ($this->features->isEnabled(Features::BIRTHDAY)) {
            $builder->add('birthday', Type\DateTimeType::class, [
                'label'    => t('field.birthday', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'autocomplete' => 'bday',
                ],
            ]);
        }

        $formModifier = function (FormInterface $form, CustomerInterface $customer, $hasParent): void {
            $form
                ->add('customerGroup', CustomerGroupChoiceType::class, [
                    'allow_new' => true,
                    'disabled'  => $hasParent,
                ])
                ->add('companyNumber', Type\TextType::class, [
                    'label'    => t('customer.field.company_number', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $hasParent,
                ])
                ->add('vatNumber', VatNumberType::class, [
                    'disabled' => $hasParent,
                ])
                ->add('vatValid', Type\CheckboxType::class, [
                    'label'    => t('pricing.field.vat_valid', [], 'EkynaCommerce'),
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
                    'label'       => t('customer.field.default_payment_method', [], 'EkynaCommerce'),
                    'required'    => false,
                    'public'      => false,
                    'credit'      => false,
                    'outstanding' => false,
                    'disabled'    => $hasParent,
                    'help'        => t('customer.help.default_payment_method', [], 'EkynaCommerce'),
                ])
                ->add('paymentMethods', PaymentMethodChoiceType::class, [
                    'label'       => t('customer.field.payment_methods', [], 'EkynaCommerce'),
                    'required'    => false,
                    'multiple'    => true,
                    'public'      => false,
                    'credit'      => false,
                    'outstanding' => false,
                    'disabled'    => $hasParent,
                    'help'        => t('customer.help.payment_methods', [], 'EkynaCommerce'),
                ])
                ->add('outstandingLimit', Type\MoneyType::class, [
                    'label'    => t('sale.field.outstanding_limit', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'currency' => $customer->getCurrency(),
                    'disabled' => $hasParent,
                ])
                ->add('outstandingOverflow', Type\CheckboxType::class, [
                    'label'    => t('customer.field.outstanding_overflow', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $hasParent,
                    'help'     => t('customer.help.outstanding_overflow', [], 'EkynaCommerce'),
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);

            if (!$hasParent) {
                return;
            }

            $form->add('canReadParentOrders', Type\CheckboxType::class, [
                'label'    => t('customer.field.can_read_parent_orders', [], 'EkynaCommerce'),
                'required' => false,
                'disabled' => !$hasParent,
                'help'     => t('customer.help.can_read_parent_orders', [], 'EkynaCommerce'),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier): void {
            /** @var CustomerInterface $customer */
            $customer = $event->getData();

            $formModifier($event->getForm(), $customer, $customer->hasParent());
        });

        $builder
            ->get('parent')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier): void {
                $customer = $event->getForm()->getParent()->getData();
                /** @var CustomerInterface $customer */
                $parent = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $customer, null !== $parent);
            });
    }
}
