<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Newsletter\Entity\AudienceTranslation;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

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
        $gatewayNames = $this->registry->getNames();

        $builder
            ->add('gateway', Type\ChoiceType::class, [
                'label'   => 'ekyna_commerce.field.factory_name',
                'choices' => array_combine(array_map(function ($name) {
                    return mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
                }, $gatewayNames), $gatewayNames),
                'select2' => false,
            ])
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
