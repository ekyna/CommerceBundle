<?php

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\CommerceBundle\Model\Units;

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
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        if (Units::isValid($value)) {
            $value = Units::getLabel($value);
        } else {
            $value = 'ekyna_core.value.undefined';
        }

        $view->vars['trans_domain'] = null;
        $view->vars['trans_params'] = [];
        $view->vars['value'] = $value;
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'text';
    }
}