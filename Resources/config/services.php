<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\EventListener\LogoutEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SecurityEventListener;
use Ekyna\Bundle\CommerceBundle\Install\CommerceInstaller;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Bundle\CommerceBundle\Service\Routing\RoutingLoader;
use Ekyna\Bundle\CommerceBundle\Service\Security\TicketAttachmentVoter;
use Ekyna\Bundle\CommerceBundle\Service\Security\TicketMessageVoter;
use Ekyna\Bundle\CommerceBundle\Service\Security\TicketVoter;
use Ekyna\Bundle\CommerceBundle\Service\Settings\CommerceSettingsSchema;
use Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler\UploaderPass;
use Ekyna\Bundle\ResourceBundle\Service\Uploader\Uploader;
use Ekyna\Bundle\SettingBundle\DependencyInjection\Compiler\RegisterSchemasPass;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Listener\LoadMetadataListener;
use Ekyna\Component\Commerce\Features;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Features
        ->set('ekyna_commerce.features', Features::class)
            ->args([
                abstract_arg('Features configuration'),
            ])
            ->tag('twig.runtime')

        // Cache
        ->set('ekyna_commerce.cache')
            ->parent('cache.app')
            ->private()
            ->tag('cache.pool', ['clearer' => 'cache.default_clearer'])

        // PSR-16 Cache
        ->set('ekyna_commerce.cache.psr16', Psr16Cache::class)
            ->args([
                service('ekyna_commerce.cache'),
            ])

        // Setting schema
        ->set('ekyna_commerce.setting', CommerceSettingsSchema::class)
            ->tag(RegisterSchemasPass::TAG, ['namespace' => 'commerce', 'position' => 5])

        // Security listener
        ->set('ekyna_commerce.listener.security', SecurityEventListener::class)
            ->args([
                service('ekyna_commerce.provider.cart'),
                service('ekyna_commerce.provider.customer'),
                service('ekyna_commerce.provider.currency'),
                service('ekyna_commerce.provider.country'),
                service('router'),
            ])
            ->tag('kernel.event_listener', [
                'dispatcher' => 'security.event_dispatcher.main',
                'event'      => LoginSuccessEvent::class,
                'method'     => 'onLoginSuccess',
                'priority'   => 0,
            ])

        // Security Logout event listener
        ->set('ekyna_commerce.listener.logout', LogoutEventSubscriber::class)
            ->args([
                service('ekyna_commerce.provider.cart'),
                service('ekyna_commerce.provider.customer'),
            ])
            ->tag('kernel.event_subscriber')

        // Security Logout event listener
        ->set('ekyna_commerce.listener.logout', LogoutEventSubscriber::class)


        // Ticket security voter
        ->set('ekyna_commerce.security_voter.ticket', TicketVoter::class)
            ->tag('security.voter')

        // Ticket message security voter
        ->set('ekyna_commerce.security_voter.ticket_message', TicketMessageVoter::class)
            ->tag('security.voter')

        // Ticket attachment security voter
        ->set('ekyna_commerce.security_voter.ticket_attachment', TicketAttachmentVoter::class)
            ->tag('security.voter')

        // Load metadata event listener
        ->set('ekyna_commerce.listener.orm.load_metadata', LoadMetadataListener::class)
            ->tag('doctrine.event_listener', [
                'event'      => Events::loadClassMetadata,
                'connection' => 'default',
                'priority'   => 99,
            ])

        // Mailer
        ->set('ekyna_commerce.mailer', Mailer::class)
            ->args([
                service('ekyna_admin.factory.mailer'),
                service('twig'),
                service('translator'),
                service('ekyna_setting.manager'),
                service('ekyna_commerce.factory.document_renderer'),
                service('ekyna_commerce.renderer.shipment_label'),
                service('ekyna_commerce.renderer.subject_label'),
                service('ekyna_commerce.helper.subject'),
                service('ekyna_commerce.filesystem'),
            ])

        // Routing loader
        ->set('ekyna_commerce.loader.routing', RoutingLoader::class)
            ->args([
                service('ekyna_commerce.features'),
                param('ekyna_user.account_routing_prefix'),
                param('kernel.environment'),
            ])
            ->tag('routing.loader')

        // Filesystem
        ->alias('ekyna_commerce.filesystem', 'oneup_flysystem.local_commerce_filesystem')

        // Uploader
        ->set('ekyna_commerce.uploader', Uploader::class)
            ->args([
                service('ekyna_resource.filesystem.tmp'),
                service('ekyna_commerce.filesystem'),
            ])
            ->tag(UploaderPass::UPLOADER_TAG)

        // Installer
        ->set('ekyna_commerce.installer', CommerceInstaller::class)
            ->args([
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.factory.factory'),
                service('ekyna_resource.manager.factory'),
                service('translator'),
                param('ekyna_commerce.default.country'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('ekyna_install.installer', ['priority' => 97])
    ;
};
