<?php

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\CommerceBundle\Model\NotificationTypes as BTypes;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes as CTypes;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NotificationsType
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        $view->vars['types'] = BTypes::getChoices([CTypes::MANUAL]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label', 'ekyna_commerce.notification.label.plural');
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'commerce_notifications';
    }
}
