<?php

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VatDisplayModeType
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VatDisplayModeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        if (null !== $value) {
            $view->vars['value'] = VatDisplayModes::getLabel($value);
            $view->vars['theme'] = VatDisplayModes::getTheme($value);
        } else {
            $view->vars['value'] = 'ekyna_core.field.default';
            $view->vars['theme'] = 'default';
        }
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label', 'ekyna_commerce.pricing.field.vat_display_mode');
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'vat_display_mode';
    }
}