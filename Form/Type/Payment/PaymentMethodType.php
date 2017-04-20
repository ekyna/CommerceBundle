<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CommerceBundle\Form\EventListener\PaymentMethodTypeSubscriber;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MessagesType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Payment\Entity;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentMethodType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodType extends AbstractResourceType
{
    private RegistryInterface $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('factoryName', GatewayFactoriesChoiceType::class, [
                'label'                     => t('field.factory_name', [], 'EkynaCommerce'),
                'choice_translation_domain' => 'PayumBundle',
                'disabled'                  => true,
            ])
            ->add('media', MediaChoiceType::class, [
                'label' => t('field.image', [], 'EkynaUi'),
                'types' => MediaTypes::IMAGE,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => PaymentMethodTranslationType::class,
                'form_options'   => [
                    'data_class' => Entity\PaymentMethodTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('mentions', MentionsType::class, [
                'mention_class'     => Entity\PaymentMethodMention::class,
                'translation_class' => Entity\PaymentMethodMentionTranslation::class,
            ])
            ->add('messages', MessagesType::class, [
                'message_class'     => Entity\PaymentMessage::class,
                'translation_class' => Entity\PaymentMessageTranslation::class,
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('available', Type\CheckboxType::class, [
                'label'    => t('field.front_office', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('private', Type\CheckboxType::class, [
                'label'    => t('payment_method.field.private', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('defaultCurrency', Type\CheckboxType::class, [
                'label'    => t('payment_method.field.use_default_currency', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('currencies', CurrencyChoiceType::class, [
                'enabled'  => false,
                'multiple' => true,
                'required' => false,
            ]);

        $builder->addEventSubscriber(new PaymentMethodTypeSubscriber($this->registry));
    }
}
