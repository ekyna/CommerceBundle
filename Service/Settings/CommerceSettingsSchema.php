<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Settings;

use Ekyna\Bundle\CommerceBundle\Form\Type\Setting\ShipmentLabelType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\SettingBundle\Form\Type\I18nParameterType;
use Ekyna\Bundle\SettingBundle\Model\I18nParameter;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\LocalizedSchemaInterface;
use Ekyna\Bundle\SettingBundle\Schema\LocalizedSchemaTrait;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CommerceSettingsSchema
 * @package Ekyna\Bundle\CommerceBundle\Service\Settings
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceSettingsSchema extends AbstractSchema implements LocalizedSchemaInterface
{
    use LocalizedSchemaTrait;

    /**
     * @inheritDoc
     */
    public function buildSettings(SettingsBuilder $builder)
    {
        $labelDefaults = [
            'size'     => 'A4',
            'width'    => null,
            'height'   => null,
            'margin'   => null,
            'download' => false,
        ];

        $labelResolver = new OptionsResolver();
        $labelResolver
            ->setDefaults($labelDefaults)
            ->setAllowedTypes('size', ['string', 'null'])
            ->setAllowedTypes('width', ['int', 'null'])
            ->setAllowedTypes('height', ['int', 'null'])
            ->setAllowedTypes('margin', ['int', 'null'])
            ->setAllowedTypes('download', ['bool', 'null']);

        $builder
            ->setDefaults(array_merge([
                'invoice_footer'  => $this->createI18nParameter('<p>Default invoice footer</p>'),
                'email_signature' => $this->createI18nParameter('<p>Default email signature</p>'),
                'shipment_label'  => $labelDefaults,
            ], $this->defaults))
            ->setAllowedTypes('invoice_footer', I18nParameter::class)
            ->setAllowedTypes('email_signature', I18nParameter::class)
            ->setAllowedTypes('shipment_label', 'array')
            ->setNormalizer('shipment_label', function (Options $options, $value) use ($labelResolver) {
                return $labelResolver->resolve($value);
            });
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoice_footer', I18nParameterType::class, [
                'label'        => 'ekyna_commerce.setting.invoice_footer',
                'form_type'    => TinymceType::class,
                'form_options' => [
                    'label'       => false,
                    'theme'       => 'simple',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ],
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('email_signature', I18nParameterType::class, [
                'label'        => 'ekyna_commerce.setting.email_signature',
                'form_type'    => TinymceType::class,
                'form_options' => [
                    'label'       => false,
                    'theme'       => 'simple',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ],
                'constraints'  => [
                    new Assert\Valid(),
                ],
            ])
            ->add('shipment_label', ShipmentLabelType::class, [
                'label'       => 'ekyna_commerce.setting.shipment_label.label',
                'required'    => false,
                'constraints' => [
                    new Assert\Valid(),
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return 'ekyna_commerce.label';
    }

    /**
     * @inheritDoc
     */
    public function getShowTemplate()
    {
        return '@EkynaCommerce/Admin/Settings/show.html.twig';
    }

    /**
     * @inheritDoc
     */
    public function getFormTemplate()
    {
        return '@EkynaCommerce/Admin/Settings/form.html.twig';
    }
}
