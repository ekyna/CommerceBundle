<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Component\Commerce\Common\Model\MessageInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessageType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class MessageType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => MessageTranslationType::class,
                'form_options' => [
                    'data_class' => $options['translation_class'],
                ],
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {
                /** @var MessageInterface $data */
                $data = $event->getData();

                /** @var \Ekyna\Component\Commerce\Common\Model\MessageTranslationInterface $translation */
                $translations = $data->getTranslations();
                foreach ($translations as $translation) {
                    if (0 == strlen($translation->getContent())) {
                        $translations->removeElement($translation);
                    }
                }
            }, 2048);
        ;
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $message = $form->getData();

        $view->vars['state'] = $message instanceof MessageInterface ? $message->getState() : null;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('translation_class')
            ->setAllowedTypes('translation_class', 'string');
    }
}
