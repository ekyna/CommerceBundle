<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BNotifications;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CNotifications;
use Ekyna\Component\Commerce\Features;
use libphonenumber\PhoneNumberType as PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ProfileType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProfileType extends AbstractType
{
    private Features $features;
    private string   $customerClass;

    public function __construct(Features $features, string $customerClass)
    {
        $this->features = $features;
        $this->customerClass = $customerClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', Type\EmailType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'disabled' => true,
                'attr'     => [
                    'autocomplete' => 'email',
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
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'save'   => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => t('button.save', [], 'EkynaUi'),
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'label'        => t('button.cancel', [], 'EkynaUi'),
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $options['cancel_path'],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($this->features->isEnabled(Features::BIRTHDAY)) {
            $builder->add('birthday', Type\DateTimeType::class, [
                'label'    => t('field.birthday', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'autocomplete' => 'bday',
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var CustomerInterface $customer */
            $customer = $event->getData();
            $form = $event->getForm();

            $form
                ->add('company', Type\TextType::class, [
                    'label'    => t('field.company', [], 'EkynaUi'),
                    'required' => false,
                    'disabled' => $customer->hasParent(),
                    'attr'     => [
                        'maxlength'    => 35,
                        'autocomplete' => 'organization',
                    ],
                ])
                ->add('vatNumber', VatNumberType::class, [
                    'disabled' => $customer->hasParent(),
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'    => $this->customerClass,
                'csrf_token_id' => 'information',
                'cancel_path'   => null,
            ])
            ->setAllowedTypes('cancel_path', 'string');
    }
}
