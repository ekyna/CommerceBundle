<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\CommerceBundle\Entity\TicketMessage;
use Ekyna\Bundle\CommerceBundle\Model\TicketStates;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\TicketNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Model\Actions;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TicketNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketNormalizer extends BaseNormalizer
{
    use TicketNormalizerTrait;

    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritdoc
     *
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $ticket
     */
    public function normalize($ticket, $format = null, array $context = [])
    {
        $data = parent::normalize($ticket, $format, $context);

        if ($this->contextHasGroup(['Default', 'Ticket'], $context)) {
            $admin = isset($context['admin']) ? $context['admin'] : false;
            $data = array_replace($data, [
                'state_badge' => $this->buildStateBadge($ticket->getState(), $admin),
                'message'     => $this->isGranted(Actions::CREATE, (new TicketMessage())->setTicket($ticket)),
                'edit'        => $this->isGranted(Actions::EDIT, $ticket),
                'remove'      => $this->isGranted(Actions::DELETE, $ticket),
                'in_charge'   => null,
            ]);

            $inCharge = $ticket->getInCharge();
            if ($inCharge && $inCharge->hasShortName()) {
                $data['in_charge'] = $inCharge->getShortName();
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function filterMessages(TicketInterface $ticket)
    {
        return $ticket->getMessages()->filter(function (TicketMessageInterface $message) {
            return $this->isGranted(Actions::VIEW, $message);
        })->toArray();
    }

    /**
     * Builds the state badge.
     *
     * @param string $state
     * @param bool   $admin
     *
     * @return string
     */
    private function buildStateBadge($state, bool $admin = false)
    {
        return sprintf(
            '<span class="label label-%s">%s</span>',
            TicketStates::getTheme($state, $admin),
            $this->translator->trans(TicketStates::getLabel($state))
        );
    }
}
