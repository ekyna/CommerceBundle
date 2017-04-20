<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CommerceBundle\Entity\NotifyModelTranslation;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method NotifyModelTranslation translate($locale = null, $create = false)
 * @method NotifyModelTranslation[] getTranslations()
 */
interface NotifyModelInterface extends TranslatableInterface
{
    public function getType(): ?string;

    public function setType(string $type): NotifyModelInterface;

    /**
     * Returns whether to include the payment message.
     */
    public function isPaymentMessage(): ?bool;

    /**
     * Sets whether to include the payment message.
     */
    public function setPaymentMessage(?bool $include): NotifyModelInterface;

    /**
     * Returns whether to include the shipment message.
     */
    public function isShipmentMessage(): ?bool;

    /**
     * Sets whether to include the shipment message.
     */
    public function setShipmentMessage(?bool $include): NotifyModelInterface;

    /**
     * Returns which type of view to include.
     */
    public function getIncludeView(): ?string;

    /**
     * Sets which type of view to include.
     */
    public function setIncludeView(?string $mode): NotifyModelInterface;

    /**
     * Returns the document types.
     */
    public function getDocumentTypes(): ?array;

    /**
     * Sets the document types.
     */
    public function setDocumentTypes(?array $types): NotifyModelInterface;

    /**
     * Returns the enabled.
     */
    public function isEnabled(): bool;

    /**
     * Sets the enabled.
     */
    public function setEnabled(bool $enabled): NotifyModelInterface;

    /**
     * Returns the translated subject.
     */
    public function getSubject(): ?string;

    /**
     * Returns the translated message.
     */
    public function getMessage(): ?string;
}
