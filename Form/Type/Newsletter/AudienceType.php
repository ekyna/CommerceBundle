<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Newsletter\Entity\AudienceTranslation;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Model\AudienceInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function array_combine;
use function array_filter;
use function array_map;
use function mb_convert_case;
use function Symfony\Component\Translation\t;

/**
 * Class AudienceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AudienceType extends AbstractResourceType
{
    private GatewayRegistry $registry;

    public function __construct(GatewayRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
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
                'label'    => t('field.factory_name', [], 'EkynaCommerce'),
                'choices'  => array_combine(array_map(function ($name) {
                    return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
                }, $gatewayNames), $gatewayNames),
                'select2'  => false,
                'disabled' => $disabled,
            ]);
        });

        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('public', Type\CheckboxType::class, [
                'label'    => t('audience.field.public', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('default', Type\CheckboxType::class, [
                'label'    => t('field.default', [], 'EkynaUi'),
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
