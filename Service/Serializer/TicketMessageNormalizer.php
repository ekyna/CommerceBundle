<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\TicketMessageNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Support\Entity\TicketAttachment;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Action\Permission;

/**
 * Class TicketMessageNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageNormalizer extends BaseNormalizer
{
    use TicketNormalizerTrait;

    /**
     * @inheritDoc
     *
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup(['Default', 'Ticket', 'Message'], $context)) {
            $data = array_replace($data, [
                'admin'      => null !== $object->getAdmin(),
                'attachment' => $this->isGranted(Permission::CREATE, (new TicketAttachment())->setMessage($object)),
                'edit'       => $this->isGranted(Permission::UPDATE, $object),
                'remove'     => $this->isGranted(Permission::DELETE, $object),
            ]);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function filterAttachments(TicketMessageInterface $message): array
    {
        return $message->getAttachments()->filter(function (TicketAttachmentInterface $attachment) {
            return $this->isGranted(Permission::READ, $attachment);
        })->toArray();
    }
}
