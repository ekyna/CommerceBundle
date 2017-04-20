<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

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
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if (null !== $value) {
            $view->vars['value'] = VatDisplayModes::getLabel($value);
            $view->vars['theme'] = VatDisplayModes::getTheme($value);
        } else {
            $view->vars['value'] = t('field.default', [], 'EkynaUi');
            $view->vars['theme'] = 'default';
        }
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label' => t('pricing.field.vat_display_mode', [], 'EkynaCommerce'),
        ]);
    }

    public static function getName(): string
    {
        return 'commerce_vat_mode';
    }
}
