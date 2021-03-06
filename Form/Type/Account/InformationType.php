<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BNotifications;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CNotifications;
use Ekyna\Component\Commerce\Features;
use libphonenumber\PhoneNumberType as PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InformationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InformationType extends AbstractType
{
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
     * @param string   $customerClass
     * @param Features $features
     */
    public function __construct(Features $features, string $customerClass)
    {
        $this->features = $features;
        $this->customerClass = $customerClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\EmailType::class, [
                'label'    => 'ekyna_core.field.email',
                'disabled' => true,
                'attr'     => [
                    'autocomplete' => 'email',
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
            ->add('currency', CurrencyChoiceType::class)
            ->add('locale', LocaleChoiceType::class)
            ->add('notifications', ConstantChoiceType::class, [
                'label'    => 'ekyna_commerce.notification.label.plural',
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
                            'label'        => 'ekyna_core.button.save',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
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
                'label'    => 'ekyna_core.field.birthday',
                'required' => false,
                'format'   => 'dd/MM/yyyy', // TODO localized format
                'attr'     => [
                    'autocomplete' => 'bday',
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
            $customer = $event->getData();
            $form = $event->getForm();

            $form
                ->add('company', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.company',
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

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
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
