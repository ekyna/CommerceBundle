<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Support;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Repository\TicketRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class TicketRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Support
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketRenderer
{
    private const CONFIG_DEFAULTS = [
        'standalone' => false,
        'admin'      => false,
        'new'        => null,
    ];

    private TicketRepositoryInterface $repository;
    private NormalizerInterface       $normalizer;
    private TranslatorInterface       $translator;
    private Environment               $twig;


    public function __construct(
        TicketRepositoryInterface $repository,
        NormalizerInterface       $normalizer,
        TranslatorInterface       $translator,
        Environment               $twig
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->translator = $translator;
        $this->twig = $twig;
    }

    /**
     * Renders the tickets for the given sources.
     *
     * @param CustomerInterface|OrderInterface|QuoteInterface $source
     * @param array                                           $config
     *
     * @return string
     */
    public function renderTickets($source, array $config = []): string
    {
        $config = array_replace(self::CONFIG_DEFAULTS, $config);

        if ($source instanceof CustomerInterface) {
            $tickets = $this->repository->findByCustomer($source, $config['admin']);
        } elseif ($source instanceof OrderInterface) {
            $tickets = $this->repository->findByOrder($source, $config['admin']);
        } elseif ($source instanceof QuoteInterface) {
            $tickets = $this->repository->findByQuote($source, $config['admin']);
        } else {
            throw new UnexpectedValueException(
                sprintf(
                    'Expected instance of %s, %s or %s.',
                    CustomerInterface::class,
                    OrderInterface::class,
                    QuoteInterface::class
                )
            );
        }

        $this->addRoutes($config);

        return $this->twig->render('@EkynaCommerce/Js/support.html.twig', [
            'tickets' => $this->normalize($tickets, $config['admin']),
            'trans'   => $this->getTranslations(),
            'config'  => $config,
        ]);
    }

    /**
     * Renders the ticket.
     *
     * @param TicketInterface $ticket
     * @param array           $config
     *
     * @return string
     */
    public function renderTicket(TicketInterface $ticket, array $config = []): string
    {
        $config = array_replace(self::CONFIG_DEFAULTS, $config);

        $this->addRoutes($config);

        $config['standalone'] = true;

        return $this->twig->render('@EkynaCommerce/Js/ticket.html.twig', [
            'ticket' => $this->normalize($ticket, $config['admin']),
            'trans'  => $this->getTranslations(),
            'config' => $config,
        ]);
    }

    /**
     * Adds routes to the config.
     *
     * @param array $config
     */
    private function addRoutes(array &$config): void
    {
        if ($config['admin']) {
            $config['routes'] = [
                'ticket'     => 'admin_ekyna_commerce_ticket',
                'message'    => 'admin_ekyna_commerce_ticket_message',
                'attachment' => 'admin_ekyna_commerce_ticket_attachment',
                'order'      => 'admin_ekyna_commerce_order',
                'quote'      => 'admin_ekyna_commerce_quote',
                'customer'   => 'admin_ekyna_commerce_customer',
            ];

            return;
        }

        $config['routes'] = [
            'ticket'     => 'ekyna_commerce_account_ticket',
            'message'    => 'ekyna_commerce_account_ticket_message',
            'attachment' => 'ekyna_commerce_account_ticket_attachment',
            'order'      => 'ekyna_commerce_account_order',
            'quote'      => 'ekyna_commerce_account_quote',
        ];
    }

    /**
     * Normalizes the given data.
     *
     * @param mixed $data
     * @param bool  $admin
     *
     * @return array
     */
    private function normalize($data, bool $admin): array
    {
        return $this->normalizer->normalize($data, 'json', [
            'groups' => ['Default', 'Ticket'],
            'admin'  => $admin,
        ]);
    }

    /**
     * Returns the translations.
     *
     * @return array
     */
    private function getTranslations(): array
    {
        return [
            'create'     => $this->translator->trans('button.create', [], 'EkynaUi'),
            'edit'       => $this->translator->trans('button.edit', [], 'EkynaUi'),
            'remove'     => $this->translator->trans('button.remove', [], 'EkynaUi'),
            'download'   => $this->translator->trans('button.download', [], 'EkynaUi'),
            'attachment' => $this->translator->trans('attachment.button.new', [], 'EkynaCommerce'),
            'close'      => $this->translator->trans('ticket.button.close', [], 'EkynaCommerce'),
            'open'       => $this->translator->trans('ticket.button.open', [], 'EkynaCommerce'),
            'answer'     => $this->translator->trans('ticket.button.answer', [], 'EkynaCommerce'),
            'no_ticket'  => $this->translator->trans('ticket.alert.no_item', [], 'EkynaCommerce'),
            'customer'   => $this->translator->trans('customer.label.singular', [], 'EkynaCommerce'),
            'in_charge'  => $this->translator->trans('customer.field.in_charge', [], 'EkynaCommerce'),
            'created_at' => $this->translator->trans('ticket.field.created_at', [], 'EkynaCommerce'),
            'updated_at' => $this->translator->trans('ticket.field.updated_at', [], 'EkynaCommerce'),
            'closed_at'  => $this->translator->trans('field.closed_at', [], 'EkynaUi'),
            'orders'     => $this->translator->trans('order.label.plural', [], 'EkynaCommerce'),
            'quotes'     => $this->translator->trans('quote.label.plural', [], 'EkynaCommerce'),
            'tags'       => $this->translator->trans('field.tags', [], 'EkynaUi'),
        ];
    }
}
