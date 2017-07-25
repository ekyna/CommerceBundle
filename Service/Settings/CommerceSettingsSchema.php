<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Settings;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;

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
        $builder
            ->setDefaults(array_merge([
                'invoice_footer' => '<p>Default invoice footer</p>',
            ], $this->defaults))
            ->setAllowedTypes('invoice_footer', 'string');
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
            ]);
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
        return 'EkynaCommerceBundle:Admin/Settings:show.html.twig';
    }

    /**
     * @inheritdoc
     */
    public function getFormTemplate()
    {
        return 'EkynaCommerceBundle:Admin/Settings:form.html.twig';
    }
}
