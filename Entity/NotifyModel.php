<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Resource\Model\AbstractTranslatable;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method NotifyModelTranslation translate($locale = null, $create = false)
 */
class NotifyModel extends AbstractTranslatable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $paymentMessage;

    /**
     * @var bool
     */
    private $shipmentMessage;

    /**
     * @var string
     */
    private $includeView;

    /**
     * @var array
     */
    private $documentTypes;

    /**
     * @var bool
     */
    private $enabled = false;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->type === NotificationTypes::MANUAL) {
            return $this->getSubject();
        }

        return sprintf('ekyna_commerce.notify.type.%s.label', $this->type);
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return NotifyModel
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns whether to include the payment message.
     *
     * @return bool|null
     */
    public function isPaymentMessage(): ?bool
    {
        return $this->paymentMessage;
    }

    /**
     * Sets whether to include the payment message.
     *
     * @param bool|null $include
     *
     * @return NotifyModel
     */
    public function setPaymentMessage(bool $include = null): self
    {
        $this->paymentMessage = $include;

        return $this;
    }

    /**
     * Returns whether to include the shipment message.
     *
     * @return bool|null
     */
    public function isShipmentMessage(): ?bool
    {
        return $this->shipmentMessage;
    }

    /**
     * Sets whether to include the shipment message.
     *
     * @param bool|null $include
     *
     * @return NotifyModel
     */
    public function setShipmentMessage(bool $include = null): self
    {
        $this->shipmentMessage = $include;

        return $this;
    }

    /**
     * Returns the include view.
     *
     * @return string|null
     */
    public function getIncludeView(): ?string
    {
        return $this->includeView;
    }

    /**
     * Sets the include view.
     *
     * @param string|null $mode
     *
     * @return NotifyModel
     */
    public function setIncludeView(string $mode = null): self
    {
        $this->includeView = $mode;

        return $this;
    }

    /**
     * Returns the document types.
     *
     * @return array|null
     */
    public function getDocumentTypes(): ?array
    {
        return $this->documentTypes;
    }

    /**
     * Sets the document types.
     *
     * @param array|null $types
     *
     * @return NotifyModel
     */
    public function setDocumentTypes(array $types = null): self
    {
        $this->documentTypes = $types;

        return $this;
    }

    /**
     * Returns the enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Sets the enabled.
     *
     * @param bool $enabled
     *
     * @return NotifyModel
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Returns the translated subject.
     *
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->translate()->getSubject();
    }

    /**
     * Returns the translated message.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->translate()->getMessage();
    }
}
