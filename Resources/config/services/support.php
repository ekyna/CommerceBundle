<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Command\SupportNotifyCommand;
use Ekyna\Bundle\CommerceBundle\Controller\Account;
use Ekyna\Bundle\CommerceBundle\Dashboard\SupportWidget;
use Ekyna\Bundle\CommerceBundle\EventListener\TicketEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\TicketMessageEventSubscriber;
use Ekyna\Bundle\CommerceBundle\Factory\TicketFactory;
use Ekyna\Bundle\CommerceBundle\Service\Support\TicketRenderer;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\TicketAttachmentEventSubscriber;
use Ekyna\Component\Commerce\Common\Generator\DateNumberGenerator;
use Ekyna\Component\Commerce\Support\Resolver\TicketStateResolver;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Account ticket controller
    $services
        ->set('ekyna_commerce.controller.account.ticket.abstract', Account\AbstractTicketController::class)
        ->abstract(true)
        ->args([
            service('ekyna_resource.factory.factory'),
            service('ekyna_resource.repository.factory'),
            service('ekyna_resource.manager.factory'),
            service('serializer'),
            service('security.authorization_checker'),
            service('router'),
            service('form.factory'),
            service('twig'),
            service('ekyna_ui.modal.renderer'),
        ])
        ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')]);

    // Account ticket controller
    $services
        ->set('ekyna_commerce.controller.account.ticket', Account\TicketController::class)
        ->parent('ekyna_commerce.controller.account.ticket.abstract')
        ->alias(Account\TicketController::class, 'ekyna_commerce.controller.account.ticket')->public();

    // Account ticket message controller
    $services
        ->set('ekyna_commerce.controller.account.ticket_message', Account\TicketMessageController::class)
        ->parent('ekyna_commerce.controller.account.ticket.abstract')
        ->alias(Account\TicketMessageController::class, 'ekyna_commerce.controller.account.ticket_message')->public();

    // Account ticket attachment controller
    $services
        ->set('ekyna_commerce.controller.account.ticket_attachment', Account\TicketAttachmentController::class)
        ->parent('ekyna_commerce.controller.account.ticket.abstract')
        ->call('setFilesystem', [service('ekyna_commerce.filesystem')])
        ->alias(Account\TicketAttachmentController::class, 'ekyna_commerce.controller.account.ticket_attachment')
        ->public();

    // Ticket number generator
    $services
        ->set('ekyna_commerce.generator.ticket_number', DateNumberGenerator::class)
        ->args([8, '\Tym', param('kernel.debug')])
        ->call('setStorage', [
            expr("parameter('kernel.project_dir')~'/var/data/ticket_number'"),
        ]);

    // Ticket factory
    $services
        ->set('ekyna_commerce.factory.ticket', TicketFactory::class)
        ->args([
            service('ekyna_commerce.factory.ticket_message'),
            service('ekyna_admin.provider.user'),
            service('ekyna_commerce.resolver.in_charge'),
            service('request_stack'),
            service('ekyna_resource.repository.factory'),
        ]);

    // Ticket state resolver
    $services
        ->set('ekyna_commerce.resolver.state.ticket', TicketStateResolver::class);

    // Ticket (resource) event subscriber
    $services
        ->set('ekyna_commerce.listener.ticket', TicketEventSubscriber::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
            service('ekyna_commerce.generator.ticket_number'),
            service('ekyna_commerce.resolver.state.ticket'),
        ])
        ->call('setInChargeResolver', [service('ekyna_commerce.resolver.in_charge')])
        ->tag('resource.event_subscriber');

    // Ticket message (resource) event subscriber
    $services
        ->set('ekyna_commerce.listener.ticket_message', TicketMessageEventSubscriber::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->call('setUserProvider', [service('ekyna_admin.provider.user')])
        ->call('setSettings', [service('ekyna_setting.manager')])
        ->tag('resource.event_subscriber');

    // Ticket attachment (resource) event subscriber
    $services
        ->set('ekyna_commerce.listener.ticket_attachment', TicketAttachmentEventSubscriber::class)
        ->args([
            service('ekyna_resource.orm.persistence_helper'),
        ])
        ->tag('resource.event_subscriber');

    // Ticket renderer
    $services
        ->set('ekyna_commerce.renderer.ticket', TicketRenderer::class)
        ->args([
            service('ekyna_commerce.repository.ticket'),
            service('serializer'),
            service('translator'),
            service('twig'),
        ])
        ->tag('twig.runtime');

    // Ticket notify command
    $services
        ->set('ekyna_commerce.command.ticket_notify', SupportNotifyCommand::class)
        ->args([
            service('ekyna_commerce.repository.ticket_message'),
            service('ekyna_admin.repository.user'),
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.mailer'),
        ])
        ->tag('console.command');

    // Dashboard support widget
    $services
        ->set('ekyna_commerce.dashboard.support_widget', SupportWidget::class)
        ->args([
            service('ekyna_commerce.repository.ticket'),
            service('table.factory'),
            service('request_stack'),
        ])
        ->tag('ekyna_admin.dashboard_widget');
};
