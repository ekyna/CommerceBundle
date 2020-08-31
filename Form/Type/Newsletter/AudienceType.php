<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Newsletter\Entity\AudienceTranslation;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AudienceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceType extends ResourceFormType
{
    /**
     * @var GatewayRegistry
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param GatewayRegistry $registry
     * @param string          $audienceClass
     */
    public function __construct(GatewayRegistry $registry, string $audienceClass)
    {
        parent::__construct($audienceClass);

        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var AudienceInterface $data */
            $data = $event->getData();
            $form = $event->getForm();

            $gatewayNames = $this->registry->getNames();

            $disabled = false;
            if (is_null($data->getId())) {
                $gatewayNames = array_filter($gatewayNames, function ($name) {
                    return $this->registry->get($name)->supports(GatewayInterface::INSERT_AUDIENCE);
                });
            } else {
                $disabled = true;
            }

            $form->add('gateway', Type\ChoiceType::class, [
                'label'    => 'ekyna_commerce.field.factory_name',
                'choices'  => array_combine(array_map(function ($name) {
                    return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
                }, $gatewayNames), $gatewayNames),
                'select2'  => false,
                'disabled' => $disabled,
            ]);
        });

        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('public', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.audience.field.public',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('default', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.default',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => AudienceTranslationType::class,
                'form_options'   => [
                    'data_class' => AudienceTranslation::class,
                ],
                'label'          => false,
                'error_bubbling' => false,
            ]);
    }
}
