<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Command\LoyaltyCouponsCommand;
use Ekyna\Bundle\CommerceBundle\Service\Customer\LoyaltyRenderer;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\LoyaltyLogRepository;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\LoyaltyEventSubscriber;
use Ekyna\Component\Commerce\Customer\Loyalty\CouponGenerator;
use Ekyna\Component\Commerce\Customer\Loyalty\LoyaltyLogger;
use Ekyna\Component\Commerce\Customer\Loyalty\LoyaltyUpdater;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Loyalty log repository
        ->set('ekyna_commerce.repository.loyalty_log', LoyaltyLogRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->tag('doctrine.repository_service')

        // Loyalty logger
        ->set('ekyna_commerce.logger.loyalty', LoyaltyLogger::class)
            ->args([
                service('ekyna_commerce.repository.loyalty_log'),
                service('ekyna_resource.orm.persistence_helper'),
                service('doctrine.orm.default_entity_manager'),
            ])

        // Loyalty logger
        ->set('ekyna_commerce.updater.loyalty', LoyaltyUpdater::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.logger.loyalty'),
            ])

        // Loyalty coupon generator
        ->set('ekyna_commerce.generator.loyalty_coupon', CouponGenerator::class)
            ->args([
                service('ekyna_commerce.features'),
                service('ekyna_commerce.updater.loyalty'),
                service('ekyna_commerce.repository.customer'),
                service('ekyna_commerce.factory.coupon'),
                service('ekyna_commerce.repository.coupon'),
                service('doctrine.orm.default_entity_manager'),
            ])

        // Loyalty event listener
        ->set('ekyna_commerce.listener.loyalty', LoyaltyEventSubscriber::class)
            ->args([
                service('ekyna_commerce.features'),
                service('ekyna_commerce.updater.loyalty'),
                service('ekyna_commerce.factory.amount_calculator'),
            ])
            ->tag('resource.event_subscriber') // TODO Should use kernel channel ?

        // Loyalty renderer
        ->set('ekyna_commerce.renderer.loyalty', LoyaltyRenderer::class)
            ->args([
                service('ekyna_commerce.repository.loyalty_log'),
                service('ekyna_commerce.repository.coupon'),
                service('twig'),
            ])
            ->tag('twig.runtime')

        // Loyalty coupons command
        ->set('ekyna_commerce.command.loyalty_coupons', LoyaltyCouponsCommand::class)
            ->args([
                service('ekyna_commerce.generator.loyalty_coupon'),
                service('ekyna_commerce.mailer'),
            ])
            ->tag('console.command')
    ;
};
