<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Show\Type\CurrencySubjectAmount;
use Ekyna\Bundle\CommerceBundle\Show\Type\CustomerType;
use Ekyna\Bundle\CommerceBundle\Show\Type\NotificationsType;
use Ekyna\Bundle\CommerceBundle\Show\Type\PhoneType;
use Ekyna\Bundle\CommerceBundle\Show\Type\UnitType;
use Ekyna\Bundle\CommerceBundle\Show\Type\VatDisplayModeType;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Currency subject show type
        ->set('ekyna_commerce.show_type.currency_subject', CurrencySubjectAmount::class) // TODO Rename
            ->tag('ekyna_admin.show.type')

        // Customer show type
        ->set('ekyna_commerce.show_type.customer', CustomerType::class)
            ->tag('ekyna_admin.show.type')

        // Customer show type
        ->set('ekyna_commerce.show_type.notifications', NotificationsType::class)
            ->tag('ekyna_admin.show.type')

        // Phone show type
        ->set('ekyna_commerce.show_type.phone', PhoneType::class)
            ->tag('ekyna_admin.show.type')

        // Unit show type
        ->set('ekyna_commerce.show_type.unit', UnitType::class)
            ->tag('ekyna_admin.show.type')

        // VAT display mode show type
        ->set('ekyna_commerce.show_type.vat_display_mode', VatDisplayModeType::class)
            ->tag('ekyna_admin.show.type')
    ;
};
