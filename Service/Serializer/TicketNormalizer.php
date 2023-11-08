<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Bundle\CommerceBundle\Entity\TicketMessage;
use Ekyna\Bundle\CommerceBundle\Model\TicketStates;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\TicketNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TicketNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketNormalizer extends BaseNormalizer
{
    use TicketNormalizerTrait;

    protected TranslatorInterface $translator;

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     *
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup(['Default', 'Ticket'], $context)) {
            $admin = $context['admin'] ?? false;

            $data = array_replace($data, [
                'state_badge' => $this->buildStateBadge($object->getState(), $admin),
                'message'     => $this->isGranted(Permission::CREATE, (new TicketMessage())->setTicket($object)),
                'edit'        => $this->isGranted(Permission::UPDATE, $object),
                'remove'      => $this->isGranted(Permission::DELETE, $object),
                'in_charge'   => null,
            ]);

            $inCharge = $object->getInCharge();
            if ($inCharge && $inCharge->hasShortName()) {
                $data['in_charge'] = $inCharge->getShortName();
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function filterMessages(TicketInterface $ticket): array
    {
        return $ticket->getMessages()->filter(function (TicketMessageInterface $message) {
            return $this->isGranted(Permission::READ, $message);
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
    private function buildStateBadge(string $state, bool $admin = false): string
    {
        return sprintf(
            '<span class="label label-%s">%s</span>',
            TicketStates::getTheme($state, $admin),
            TicketStates::getLabel($state)->trans($this->translator)
        );
    }
}
