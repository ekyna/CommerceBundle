<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Serializer;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\TicketAttachmentNormalizer as BaseNormalizer;
use Ekyna\Component\Resource\Model\Actions;

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
     * @param \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface $message
     */
    public function normalize($attachment, $format = null, array $context = [])
    {
        $data = parent::normalize($attachment, $format, $context);

        if ($this->contextHasGroup(['Default', 'Ticket', 'TicketMessage', 'TicketAttachment'], $context)) {
            $data = array_replace($data, [
                'edit'   => $this->isGranted(Actions::EDIT, $attachment),
                'remove' => $this->isGranted(Actions::DELETE, $attachment),
            ]);
        }

        return $data;
    }
}
