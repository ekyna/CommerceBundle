<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Settings;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommerceSettingsSchema
 * @package Ekyna\Bundle\CommerceBundle\Service\Settings
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceSettingsSchema extends AbstractSchema
{
    /**
     * @inheritdoc
     */
    public function buildSettings(SettingsBuilder $builder)
    {
        $labelDefaults = [
            'size'   => 'A4',
            'width'  => null,
            'height' => null,
            'margin' => null,
        ];

        $labelResolver = new OptionsResolver();
        $labelResolver
            ->setDefaults($labelDefaults)
            ->setAllowedTypes('size', ['string', 'null'])
            ->setAllowedTypes('width', ['int', 'null'])
            ->setAllowedTypes('height', ['int', 'null'])
            ->setAllowedTypes('margin', ['int', 'null']);

        $builder
            ->setDefaults(array_merge([
                'invoice_footer'  => '<p>Default invoice footer</p>',
                'email_signature' => '<p>Default email signature</p>',
                'shipment_label'  => $labelDefaults,
            ], $this->defaults))
            ->setAllowedTypes('invoice_footer', 'string')
            ->setAllowedTypes('email_signature', 'string')
            ->setAllowedTypes('shipment_label', 'array')
            ->setNormalizer('shipment_label', function (Options $options, $value) use ($labelResolver) {
                return $labelResolver->resolve($value);
            });
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoice_footer', TinymceType::class, [
                'label' => 'ekyna_commerce.setting.invoice_footer',
                'theme' => 'simple',
            ])
            ->add('email_signature', TinymceType::class, [
                'label' => 'ekyna_commerce.setting.email_signature',
                'theme' => 'simple',
            ]);

        $label = $builder
            ->create('shipment_label', FormType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.label',
                'required' => false,
            ])
            ->add('size', ChoiceType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.size',
                'choices'  => LabelRenderer::getSizes(),
                'required' => false,
                'select2'  => false,
            ])
            ->add('width', IntegerType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.width',
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('height', IntegerType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.height',
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('margin', IntegerType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.margin',
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ]);

        $builder->add($label);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_commerce.label';
    }

    /**
     * @inheritdoc
     */
    public function getShowTemplate()
    {
        return '@EkynaCommerce/Admin/Settings/show.html.twig';
    }

    /**
     * @inheritdoc
     */
    public function getFormTemplate()
    {
        return '@EkynaCommerce/Admin/Settings/form.html.twig';
    }
}
