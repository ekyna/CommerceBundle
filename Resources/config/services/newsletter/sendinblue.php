<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Component\Commerce\Bridge\SendInBlue\Api;
use Ekyna\Component\Commerce\Bridge\SendInBlue\Gateway;
use Ekyna\Component\Commerce\Bridge\SendInBlue\Handler;
use Ekyna\Component\Commerce\Bridge\SendInBlue\Synchronizer;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\NewsletterRegistriesPass;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // SendInBlue API
        ->set('ekyna_commerce.newsletter.api.sendinblue', Api::class)
            ->args([
                service('ekyna_commerce.logger.newsletter'),
                abstract_arg('SendInBlue API key'),
            ])

        // SendInBlue gateway
        ->set('ekyna_commerce.newsletter.gateway.sendinblue', Gateway::class)
            ->args([
                service('ekyna_commerce.newsletter.api.sendinblue'),
            ])
            ->tag(NewsletterRegistriesPass::GATEWAY_TAG)

        // SendInBlue synchronizer
        ->set('ekyna_commerce.newsletter.synchronizer.sendinblue', Synchronizer::class)
            ->parent('ekyna_commerce.newsletter.synchronizer.abstract')
            ->call('setApi', [service('ekyna_commerce.newsletter.api.sendinblue')])
            ->tag(NewsletterRegistriesPass::SYNCHRONIZER_TAG)

        // SendInBlue webhook handler
        ->set('ekyna_commerce.newsletter.handler.sendinblue', Handler::class)
            ->parent('ekyna_commerce.newsletter.webhook.abstract')
            ->tag(NewsletterRegistriesPass::HANDLER_TAG)
    ;
};

