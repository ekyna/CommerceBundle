<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Entity\NotifyModelTranslation;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', Type\ChoiceType::class, [
                'label'   => 'ekyna_core.field.type',
                'choices' => BTypes::getChoices([CTypes::MANUAL]),
            ])
            ->add('paymentMessage', Type\ChoiceType::class, [
                'label'       => 'ekyna_commerce.notify.field.payment_message',
                'placeholder' => 'ekyna_core.field.default',
                'choices'     => [
                    'ekyna_core.value.no'  => 0,
                    'ekyna_core.value.yes' => 1,
                ],
                'expanded'    => true,
                'required'    => false,
                'attr'        => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => 'ekyna_commerce.notify_model.help.payment_message',
                ],
            ])
            ->add('shipmentMessage', Type\ChoiceType::class, [
                'label'       => 'ekyna_commerce.notify.field.shipment_message',
                'placeholder' => 'ekyna_core.field.default',
                'choices'     => [
                    'ekyna_core.value.no'  => 0,
                    'ekyna_core.value.yes' => 1,
                ],
                'expanded'    => true,
                'required'    => false,
                'attr'        => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => 'ekyna_commerce.notify_model.help.shipment_message',
                ],
            ])
            ->add('includeView', Type\ChoiceType::class, [
                'label'       => 'ekyna_commerce.notify.field.include_view',
                'placeholder' => 'ekyna_core.field.default',
                'choices'     => [
                    'ekyna_commerce.notify.include_view.none'   => Notify::VIEW_NONE,
                    'ekyna_commerce.notify.include_view.before' => Notify::VIEW_BEFORE,
                    'ekyna_commerce.notify.include_view.after'  => Notify::VIEW_AFTER,
                ],
                'expanded'    => true,
                'required'    => false,
                'attr'        => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => 'ekyna_commerce.notify_model.help.include_view',
                ],
            ])
            ->add('documentTypes', Type\ChoiceType::class, [
                'label'    => 'ekyna_commerce.notify_model.field.document_types',
                'choices'  => DocumentTypes::getSaleChoices(),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                    'help_text'         => 'ekyna_commerce.notify_model.help.document_types',
                ],
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => NotifyModelTranslationType::class,
                'form_options'   => [
                    'data_class' => NotifyModelTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ]);

        // Removes empty translations
        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
            /** @var \Ekyna\Bundle\CommerceBundle\Entity\NotifyModel $model */
            $model = $event->getData();

            /** @var NotifyModelTranslation $translation */
            foreach ($model->getTranslations() as $translation) {
                if (empty($translation->getSubject()) && empty($translation->getMessage())) {
                    $model->removeTranslation($translation);
                }
            }
        }, 1024); // Before validation
    }
}
