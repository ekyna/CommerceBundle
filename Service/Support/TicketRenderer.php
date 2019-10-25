<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Support;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Repository\TicketRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @var TicketRepositoryInterface
     */
    private $repository;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param TicketRepositoryInterface $repository
     * @param NormalizerInterface       $normalizer
     * @param TranslatorInterface       $translator
     * @param EngineInterface           $templating
     */
    public function __construct(
        TicketRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        TranslatorInterface $translator,
        EngineInterface $templating
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->translator = $translator;
        $this->templating = $templating;
    }

    /**
     * Renders the tickets for the given sources.
     *
     * @param CustomerInterface|OrderInterface|QuoteInterface $source
     * @param array                                           $config
     *
     * @return string
     */
    public function renderTickets($source, array $config = [])
    {
        $config = array_replace(self::CONFIG_DEFAULTS, $config);

        if ($source instanceof CustomerInterface) {
            $tickets = $this->repository->findByCustomer($source, $config['admin']);
        } elseif ($source instanceof OrderInterface) {
            $tickets = $this->repository->findByOrder($source, $config['admin']);
        } elseif ($source instanceof QuoteInterface) {
            $tickets = $this->repository->findByQuote($source, $config['admin']);
        } else {
            throw new UnexpectedValueException(sprintf(
                'Expected instance of %s, %s or %s.',
                CustomerInterface::class,
                OrderInterface::class,
                QuoteInterface::class
            ));
        }

        $this->addRoutes($config);

        return $this->templating->render('@EkynaCommerce/Js/support.html.twig', [
            'tickets' => $this->normalize($tickets),
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
    public function renderTicket(TicketInterface $ticket, array $config = [])
    {
        $config = array_replace(self::CONFIG_DEFAULTS, $config);

        $this->addRoutes($config);

        $config['standalone'] = true;

        return $this->templating->render('@EkynaCommerce/Js/ticket.html.twig', [
            'ticket' => $this->normalize($ticket),
            'trans'  => $this->getTranslations(),
            'config' => $config,
        ]);
    }

    /**
     * Adds routes to the config.
     *
     * @param array $config
     */
    private function addRoutes(array &$config)
    {
        if ($config['admin']) {
            $config['routes'] = [
                'ticket'     => 'ekyna_commerce_ticket_admin',
                'message'    => 'ekyna_commerce_ticket_message_admin',
                'attachment' => 'ekyna_commerce_ticket_attachment_admin',
                'order'      => 'ekyna_commerce_order_admin',
                'quote'      => 'ekyna_commerce_quote_admin',
                'customer'   => 'ekyna_commerce_customer_admin',
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
     *
     * @return array
     */
    private function normalize($data)
    {
        return $this->normalizer->normalize($data, 'json', ['groups' => ['Default', 'Ticket']]);
    }

    /**
     * Returns the translations.
     *
     * @return array
     */
    private function getTranslations()
    {
        return [
            'create'     => $this->translator->trans('ekyna_core.button.create'),
            'edit'       => $this->translator->trans('ekyna_core.button.edit'),
            'remove'     => $this->translator->trans('ekyna_core.button.remove'),
            'download'   => $this->translator->trans('ekyna_core.button.download'),
            'attachment' => $this->translator->trans('ekyna_commerce.attachment.button.new'),
            'close'      => $this->translator->trans('ekyna_commerce.ticket.button.close'),
            'answer'     => $this->translator->trans('ekyna_commerce.ticket.button.answer'),
            'no_ticket'  => $this->translator->trans('ekyna_commerce.ticket.alert.no_item'),
            'customer'   => $this->translator->trans('ekyna_commerce.customer.label.singular'),
            'in_charge'  => $this->translator->trans('ekyna_commerce.customer.field.in_charge'),
            'created_at' => $this->translator->trans('ekyna_commerce.ticket.field.created_at'),
            'updated_at' => $this->translator->trans('ekyna_commerce.ticket.field.updated_at'),
            'orders'     => $this->translator->trans('ekyna_commerce.order.label.plural'),
            'quotes'     => $this->translator->trans('ekyna_commerce.quote.label.plural'),
        ];
    }
}
