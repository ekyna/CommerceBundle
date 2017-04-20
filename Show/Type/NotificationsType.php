<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class NotificationsType
 * @package Ekyna\Bundle\CommerceBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationsType extends AbstractType
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('notification.label.plural', [], 'EkynaCommerce'),
        ]);
    }

    public static function getName(): string
    {
        return 'commerce_notifications';
    }
}
