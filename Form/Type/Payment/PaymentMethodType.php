<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\EventListener\PaymentMethodTypeSubscriber;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MessagesType;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Payment\Entity;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayFactoriesChoiceType;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PaymentMethodType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodType extends ResourceFormType
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param string            $class
     * @param RegistryInterface $registry
     */
    public function __construct($class, RegistryInterface $registry)
    {
        parent::__construct($class);

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('factoryName', GatewayFactoriesChoiceType::class, [
                'label'              => 'ekyna_commerce.field.factory_name',
                'translation_domain' => null,
                'disabled'           => true,
            ])
            ->add('media', MediaChoiceType::class, [
                'label' => 'ekyna_core.field.image',
                'types' => MediaTypes::IMAGE,
            ])
            ->add('mentionTypes', ConstantChoiceType::class, [
                'label'    => 'ekyna_commerce.payment_method.field.mention_types',
                'class'    => DocumentTypes::class,
                'accessor' => 'getSaleAndInvoiceChoices',
                'multiple' => true,
                'required' => false,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => PaymentMethodTranslationType::class,
                'form_options'   => [
                    'data_class' => Entity\PaymentMethodTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('messages', MessagesType::class, [
                'message_class'     => Entity\PaymentMessage::class,
                'translation_class' => Entity\PaymentMessageTranslation::class,
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('available', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.field.front_office',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('private', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.payment_method.field.private',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('defaultCurrency', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.payment_method.field.use_default_currency',
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
