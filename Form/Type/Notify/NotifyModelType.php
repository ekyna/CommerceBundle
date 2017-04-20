<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Notify;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CommerceBundle\Entity\NotifyModelTranslation;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Notify
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ConstantChoiceType::class, [
                'label' => t('field.type', [], 'EkynaUi'),
                'class' => BTypes::class,
            ])
            ->add('paymentMessage', Type\ChoiceType::class, [
                'label'                     => t('notify.field.payment_message', [], 'EkynaCommerce'),
                'placeholder'               => t('field.default', [], 'EkynaUi'),
                'choices'                   => [
                    'value.no'  => 0,
                    'value.yes' => 1,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'expanded'                  => true,
                'required'                  => false,
                'attr'                      => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => t('notify_model.help.payment_message', [], 'EkynaCommerce'),
                ],
            ])
            ->add('shipmentMessage', Type\ChoiceType::class, [
                'label'                     => t('notify.field.shipment_message', [], 'EkynaCommerce'),
                'placeholder'               => t('field.default', [], 'EkynaUi'),
                'choices'                   => [
                    'value.no'  => 0,
                    'value.yes' => 1,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'expanded'                  => true,
                'required'                  => false,
                'attr'                      => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => t('notify_model.help.shipment_message', [], 'EkynaCommerce'),
                ],
            ])
            ->add('includeView', Type\ChoiceType::class, [
                'label'                     => t('notify.field.include_view', [], 'EkynaCommerce'),
                'placeholder'               => t('field.default', [], 'EkynaUi'),
                'choices'                   => [
                    'notify.include_view.none'   => Notify::VIEW_NONE,
                    'notify.include_view.before' => Notify::VIEW_BEFORE,
                    'notify.include_view.after'  => Notify::VIEW_AFTER,
                ],
                'choice_translation_domain' => 'EkynaCommerce',
                'expanded'                  => true,
                'required'                  => false,
                'attr'                      => [
                    'class'             => 'inline',
                    'align_with_widget' => true,
                    'help_text'         => t('notify_model.help.include_view', [], 'EkynaCommerce'),
                ],
            ])
            ->add('documentTypes', ConstantChoiceType::class, [
                'label'    => t('notify_model.field.document_types', [], 'EkynaCommerce'),
                'class'    => DocumentTypes::class,
                'accessor' => 'getSaleChoices',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                    'help_text'         => t('notify_model.field.document_types', [], 'EkynaCommerce'),
                ],
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
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
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var NotifyModelInterface $model */
            $model = $event->getData();

            foreach ($model->getTranslations() as $translation) {
                if (empty($translation->getSubject()) && empty($translation->getMessage())) {
                    $model->removeTranslation($translation);
                }
            }
        }, 1024); // Before validation
    }
}
