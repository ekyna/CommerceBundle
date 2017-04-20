<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Controller\Admin\MapController;
use Ekyna\Bundle\CommerceBundle\Service\Map\MapBuilder;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Map builder
        ->set('ekyna_commerce.builder.map', MapBuilder::class)
            ->args([
                service('ekyna_commerce.repository.customer_address'),
                service('ekyna_commerce.repository.order'),
                service('ekyna_setting.manager'),
                service('form.factory'),
            ])

        // Map controller
        ->set('ekyna_commerce.controller.admin.map', MapController::class)
            ->args([
                service('ekyna_commerce.builder.map'),
                service('twig'),
                service('ekyna_commerce.repository.customer'),
                service('ekyna_admin.menu.builder'),
            ])
            ->alias(MapController::class, 'ekyna_commerce.controller.admin.map')->public()
    ;
};
