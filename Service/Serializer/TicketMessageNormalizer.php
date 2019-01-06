<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\TicketMessageNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Support\Entity\TicketAttachment;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Model\Actions;

/**
 * Class TicketMessageNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageNormalizer extends BaseNormalizer
{
    use TicketNormalizerTrait;

    /**
     * @inheritdoc
     *
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface $message
     */
    public function normalize($message, $format = null, array $context = [])
    {
        $data = parent::normalize($message, $format, $context);

        if ($this->contextHasGroup(['Default', 'Ticket', 'Message'], $context)) {
            $data = array_replace($data, [
                'admin'      => null !== $message->getAdmin(),
                'attachment' => $this->isGranted(Actions::CREATE, (new TicketAttachment())->setMessage($message)),
                'edit'       => $this->isGranted(Actions::EDIT, $message),
                'remove'     => $this->isGranted(Actions::DELETE, $message),
            ]);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function filterAttachments(TicketMessageInterface $message)
    {
        return $message->getAttachments()->filter(function (TicketAttachmentInterface $attachment) {
            return $this->isGranted(Actions::VIEW, $attachment);
        })->toArray();
    }
}
