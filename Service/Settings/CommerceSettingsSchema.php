<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Settings;

use Ekyna\Bundle\CommerceBundle\Form\Type\Setting\ShipmentLabelType;
use Ekyna\Bundle\SettingBundle\Form\Type\I18nParameterType;
use Ekyna\Bundle\SettingBundle\Model\I18nParameter;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\LocalizedSchemaInterface;
use Ekyna\Bundle\SettingBundle\Schema\LocalizedSchemaTrait;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CommerceSettingsSchema
 * @package Ekyna\Bundle\CommerceBundle\Service\Settings
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceSettingsSchema extends AbstractSchema implements LocalizedSchemaInterface
{
    use LocalizedSchemaTrait;

    public function buildSettings(SettingsBuilder $builder): void
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoice_footer', I18nParameterType::class, [
                'label'        => t('setting.invoice_footer', [], 'EkynaCommerce'),
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
                'label'        => t('setting.email_signature', [], 'EkynaCommerce'),
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
                'label'       => t('setting.shipment_label.label', [], 'EkynaCommerce'),
                'required'    => false,
                'constraints' => [
                    new Assert\Valid(),
                ],
            ]);
    }

    public function getLabel(): TranslatableInterface
    {
        return t('label', [], 'EkynaCommerce');
    }

    public function getShowTemplate(): string
    {
        return '@EkynaCommerce/Admin/Settings/show.html.twig';
    }

    public function getFormTemplate(): string
    {
        return '@EkynaCommerce/Admin/Settings/form.html.twig';
    }
}
