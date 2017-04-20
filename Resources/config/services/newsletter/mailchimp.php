<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Component\Commerce\Bridge\Mailchimp\Api;
use Ekyna\Component\Commerce\Bridge\Mailchimp\Gateway;
use Ekyna\Component\Commerce\Bridge\Mailchimp\Handler;
use Ekyna\Component\Commerce\Bridge\Mailchimp\Synchronizer;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\NewsletterRegistriesPass;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Mailchimp API
        ->set('ekyna_commerce.newsletter.api.mailchimp', Api::class)
            ->args([
                service('ekyna_commerce.logger.newsletter'),
                abstract_arg('Mailchimp API key'),
            ])

        // Mailchimp gateway
        ->set('ekyna_commerce.newsletter.gateway.mailchimp', Gateway::class)
            ->args([
                service('ekyna_commerce.newsletter.api.mailchimp'),
            ])
            ->tag(NewsletterRegistriesPass::GATEWAY_TAG)

        // Mailchimp synchronizer
        ->set('ekyna_commerce.newsletter.synchronizer.mailchimp', Synchronizer::class)
            ->parent('ekyna_commerce.newsletter.synchronizer.abstract')
            ->call('setApi', [service('ekyna_commerce.newsletter.api.mailchimp')])
            ->tag(NewsletterRegistriesPass::SYNCHRONIZER_TAG)

        // Mailchimp webhook handler
        ->set('ekyna_commerce.newsletter.handler.mailchimp', Handler::class)
            ->parent('ekyna_commerce.newsletter.webhook.abstract')
            ->tag(NewsletterRegistriesPass::HANDLER_TAG)
    ;
};

