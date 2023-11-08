<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\TicketAttachmentNormalizer as BaseNormalizer;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Resource\Action\Permission;

/**
 * Class TicketAttachmentNormalizer
 * @package Ekyna\Bundle\CommerceBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentNormalizer extends BaseNormalizer
{
    use TicketNormalizerTrait;

    /**
     * @inheritDoc
     *
     * @param TicketAttachmentInterface $message
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if (self::contextHasGroup(['Default', 'Ticket', 'TicketMessage', 'TicketAttachment'], $context)) {
            $data = array_replace($data, [
                'edit'   => $this->isGranted(Permission::UPDATE, $object),
                'remove' => $this->isGranted(Permission::DELETE, $object),
            ]);
        }

        return $data;
    }
}
