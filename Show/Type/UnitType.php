<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\CommerceBundle\Model\Units;

use function Symfony\Component\Translation\t;

/**
 * Class UnitType
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if (Units::isValid($value)) {
            $value = Units::getLabel($value);
        } else {
            $value = t('value.undefined', [], 'EkynaUi');
        }

        $view->vars['value'] = $value;
        $view->vars['trans_domain'] = null;
    }

    public function getWidgetPrefix(): ?string
    {
        return 'text';
    }

    public static function getName(): string
    {
        return 'commerce_unit';
    }
}
