<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method NotifyModelTranslation translate($locale = null, $create = false)
 */
class NotifyModel extends AbstractTranslatable implements NotifyModelInterface
{
    private ?string $type            = null;
    private ?bool   $paymentMessage  = null;
    private ?bool   $shipmentMessage = null;
    private ?string $includeView     = null;
    private ?array  $documentTypes   = null;
    private bool    $enabled         = false;


    public function __toString(): string
    {
        if ($this->type === NotificationTypes::MANUAL) {
            return $this->getSubject() ?: 'New notification model';
        }

        return sprintf('notify.type.%s.label', $this->type);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): NotifyModelInterface
    {
        $this->type = $type;

        return $this;
    }

    public function isPaymentMessage(): ?bool
    {
        return $this->paymentMessage;
    }

    public function setPaymentMessage(?bool $include): NotifyModelInterface
    {
        $this->paymentMessage = $include;

        return $this;
    }

    public function isShipmentMessage(): ?bool
    {
        return $this->shipmentMessage;
    }

    public function setShipmentMessage(?bool $include): NotifyModelInterface
    {
        $this->shipmentMessage = $include;

        return $this;
    }

    public function getIncludeView(): ?string
    {
        return $this->includeView;
    }

    public function setIncludeView(?string $mode): NotifyModelInterface
    {
        $this->includeView = $mode;

        return $this;
    }

    public function getDocumentTypes(): ?array
    {
        return $this->documentTypes;
    }

    public function setDocumentTypes(?array $types): NotifyModelInterface
    {
        $this->documentTypes = $types;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): NotifyModelInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->translate()->getSubject();
    }

    public function getMessage(): ?string
    {
        return $this->translate()->getMessage();
    }
}
