<?php

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
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
