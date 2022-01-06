<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\Command\NewsletterSynchronizeCommand;
use Ekyna\Bundle\CommerceBundle\Controller\Api\Newsletter\SubscriptionController;
use Ekyna\Bundle\CommerceBundle\Controller\Api\Newsletter\WebhookController;
use Ekyna\Bundle\CommerceBundle\Controller\Account\NewsletterController as AccountNewsletterController;
use Ekyna\Bundle\CommerceBundle\Controller\Front\NewsletterController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter\AudienceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Newsletter\NewsletterSubscriptionType;
use Ekyna\Bundle\CommerceBundle\Service\Newsletter\SubscriptionHelper;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\AudienceRepository;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\AudienceEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\MemberEventSubscriber;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\SubscriptionEventSubscriber;
use Ekyna\Component\Commerce\Newsletter\EventListener\ListenerGatewayToggler;
use Ekyna\Component\Commerce\Newsletter\Factory\AudienceFactory;
use Ekyna\Component\Commerce\Newsletter\Gateway\GatewayRegistry;
use Ekyna\Component\Commerce\Newsletter\Logger;
use Ekyna\Component\Commerce\Newsletter\Synchronizer\AbstractSynchronizer;
use Ekyna\Component\Commerce\Newsletter\Synchronizer\SynchronizerRegistry;
use Ekyna\Component\Commerce\Newsletter\Updater\AudienceUpdater;
use Ekyna\Component\Commerce\Newsletter\Webhook\AbstractHandler;
use Ekyna\Component\Commerce\Newsletter\Webhook\HandlerRegistry;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Logger
        ->set('ekyna_commerce.logger.newsletter', Logger::class) // TODO Rename to NewsletterLogger
            ->args([
                service('logger')
            ])
            ->tag('monolog.logger', ['channel' => 'newsletter'])

        // Audience factory
        ->set('ekyna_commerce.factory.audience', AudienceFactory::class)
            ->args([
                service('ekyna_commerce.generator.key'),
            ])

        // Audience repository
        ->set('ekyna_commerce.repository.audience', AudienceRepository::class)
            ->tag('doctrine.event_listener', [
                'event'      => Events::onClear,
                'connection' => 'default',
            ])

        // Gateway registry
        ->set('ekyna_commerce.newsletter.registry.gateway', GatewayRegistry::class)
            ->args([
                abstract_arg('Newsletter gateways services locator'),
                abstract_arg('Newsletter gateways services names'),
            ])

        // Abstract synchronizer
        ->set('ekyna_commerce.newsletter.synchronizer.abstract', AbstractSynchronizer::class)
            ->abstract(true)
            ->args([
                service('ekyna_commerce.factory.audience'),
                service('ekyna_commerce.repository.audience'),
                service('ekyna_commerce.factory.member'),
                service('ekyna_commerce.repository.member'),
                service('ekyna_commerce.factory.subscription'),
                service('ekyna_commerce.repository.subscription'),
                service('ekyna_commerce.listener.newsletter_gateway_listener_toggler'),
                service('ekyna_resource.event_dispatcher'),
                service('doctrine.orm.default_entity_manager'),
                service('router'),
                service('ekyna_commerce.logger.newsletter'),
            ])

        // Synchronizer registry
        ->set('ekyna_commerce.newsletter.registry.synchronizer', SynchronizerRegistry::class)
            ->args([
                abstract_arg('Newsletter synchronizers services locator'),
                abstract_arg('Newsletter synchronizers services names'),
            ])

        // Abstract webhook handler
        ->set('ekyna_commerce.newsletter.webhook.abstract', AbstractHandler::class)
            ->abstract(true)
            ->args([
                service('ekyna_commerce.repository.audience'),
                service('ekyna_commerce.factory.member'),
                service('ekyna_commerce.repository.member'),
                service('ekyna_commerce.factory.subscription'),
                service('ekyna_commerce.repository.subscription'),
                service('ekyna_commerce.listener.newsletter_gateway_listener_toggler'),
                service('ekyna_resource.event_dispatcher'),
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_commerce.logger.newsletter'),
            ])

        // Webhook handler registry
        ->set('ekyna_commerce.newsletter.registry.webhook', HandlerRegistry::class)
            ->args([
                abstract_arg('Newsletter webhook handlers services locator'),
                abstract_arg('Newsletter webhook handlers services names'),
            ])

        // Newsletter account controller
        ->set('ekyna_commerce.controller.account.newsletter', AccountNewsletterController::class)
            ->args([
                service('twig'),
            ])
            ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
            ->alias(AccountNewsletterController::class, 'ekyna_commerce.controller.account.newsletter')->public()

        // Newsletter controller
        ->set('ekyna_commerce.controller.newsletter', NewsletterController::class)
            ->args([
                service('ekyna_commerce.helper.newsletter_subscription'),
                service('router'),
                service('twig'),
            ])
            ->alias(NewsletterController::class, 'ekyna_commerce.controller.newsletter')->public()

        // Newsletter API subscription controller
        ->set('ekyna_commerce.controller.api.subscription', SubscriptionController::class)
            ->args([
                service('ekyna_commerce.repository.customer'),
                service('ekyna_commerce.helper.newsletter_subscription'),
            ])
            ->alias(SubscriptionController::class, 'ekyna_commerce.controller.api.subscription')->public()

        // Newsletter API webhook controller
        ->set('ekyna_commerce.controller.api.webhook', WebhookController::class)
            ->args([
                service('ekyna_commerce.newsletter.registry.webhook'),
            ])
            ->alias(WebhookController::class, 'ekyna_commerce.controller.api.webhook')->public()

        // Audience updater
        ->set('ekyna_commerce.updater.audience', AudienceUpdater::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.audience'),
            ])

        // Audience (resource) event listener
        ->set('ekyna_commerce.listener.audience', AudienceEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.newsletter.registry.gateway'),
                service('ekyna_commerce.updater.audience'),
            ])
            ->tag('resource.event_subscriber')

        // Member (resource) event listener
        ->set('ekyna_commerce.listener.member', MemberEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.customer'),
                service('ekyna_resource.event_dispatcher'),
            ])
            ->tag('resource.event_subscriber')

        // Subscription (resource) event listener
        ->set('ekyna_commerce.listener.subscription', SubscriptionEventSubscriber::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.newsletter.registry.gateway'),
            ])
            ->tag('resource.event_subscriber')

        // Newsletter event listeners toggler
        ->set('ekyna_commerce.listener.newsletter_gateway_listener_toggler', ListenerGatewayToggler::class)
            ->args([
                [
                    service('ekyna_commerce.listener.audience'),
                    service('ekyna_commerce.listener.member'),
                    service('ekyna_commerce.listener.subscription'),
                ]
            ])

        // Audience form type
        ->set('ekyna_commerce.form_type.audience', AudienceType::class)
            ->args([
                service('ekyna_commerce.newsletter.registry.gateway'),
                param('ekyna_commerce.class.audience'), // TODO Remove (setClass is called by resource component)
            ])
            ->tag('form.type')

        // Newsletter subscription (aka subscribe) form type
        ->set('ekyna_commerce.form_type.newsletter_subscription', NewsletterSubscriptionType::class)
            ->args([
                param('ekyna_commerce.class.audience'),
            ])
            ->tag('form.type')

        // Synchronize command
        ->set('ekyna_commerce.command.newsletter_synchronize', NewsletterSynchronizeCommand::class)
            ->args([
                service('ekyna_commerce.newsletter.registry.synchronizer'),
            ])
            ->tag('console.command')

        // Subscription helper
        ->set('ekyna_commerce.helper.newsletter_subscription', SubscriptionHelper::class)
            ->args([
                service('ekyna_commerce.repository.audience'),
                service('ekyna_commerce.repository.member'),
                service('ekyna_resource.factory.factory'),
                service('form.factory'),
                service('table.factory'),
                service('ekyna_commerce.newsletter.registry.gateway'),
                service('ekyna_resource.event_dispatcher'),
                service('validator'),
                service('doctrine.orm.default_entity_manager'),
                service('twig'),
            ])
            ->tag('twig.runtime')
    ;
};
