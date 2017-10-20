<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Entity\PaymentMessage;
use Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessage;

/**
 * Class Notification
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Notification
{
    const VIEW_NONE   = 'none';
    const VIEW_BEFORE = 'before';
    const VIEW_AFTER  = 'after';

    /**
     * @var Recipient
     */
    private $from;

    /**
     * @var ArrayCollection
     */
    private $recipients;

    /**
     * @var ArrayCollection
     */
    private $extraRecipients;

    /**
     * @var ArrayCollection
     */
    private $copies;

    /**
     * @var ArrayCollection
     */
    private $extraCopies;

    /**
     * @var ArrayCollection|InvoiceInterface[]
     */
    private $invoices;

    /**
     * @var ArrayCollection|AttachmentInterface[]
     */
    private $attachments;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var PaymentMessage
     */
    private $paymentMessage;

    /**
     * @var ShipmentMessage
     */
    private $shipmentMessage;

    /**
     * @var string
     */
    private $customMessage;

    /**
     * @var string
     */
    private $includeView;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->extraRecipients = new ArrayCollection();
        $this->copies = new ArrayCollection();
        $this->extraCopies = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->attachments = new ArrayCollection();

        $this->includeView = static::VIEW_NONE;
    }

    /**
     * Returns the from.
     *
     * @return Recipient
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the from.
     *
     * @param Recipient $from
     */
    public function setFrom(Recipient $from)
    {
        $this->from = $from;
    }

    /**
     * Returns the recipients.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Adds the recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notification
     */
    public function addRecipient(Recipient $recipient)
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients->add($recipient);
        }

        return $this;
    }

    /**
     * Removes the recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notification
     */
    public function removeRecipient(Recipient $recipient)
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
        }

        return $this;
    }

    /**
     * Returns the extra recipients.
     *
     * @return ArrayCollection|Recipient[]
     */
    public function getExtraRecipients()
    {
        return $this->extraRecipients;
    }

    /**
     * Adds the extra recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notification
     */
    public function addExtraRecipient(Recipient $recipient)
    {
        if (!$this->extraRecipients->contains($recipient)) {
            $this->extraRecipients->add($recipient);
        }

        return $this;
    }

    /**
     * Removes the extra recipient.
     *
     * @param Recipient $recipient
     *
     * @return Notification
     */
    public function removeExtraRecipient(Recipient $recipient)
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
        }

        return $this;
    }

    /**
     * Returns the copies.
     *
     * @return ArrayCollection|Recipient
     */
    public function getCopies()
    {
        return $this->copies;
    }

    /**
     * Adds the copy.
     *
     * @param Recipient $copy
     *
     * @return Notification
     */
    public function addCopy(Recipient $copy)
    {
        if (!$this->copies->contains($copy)) {
            $this->copies->add($copy);
        }

        return $this;
    }

    /**
     * Removes the copy.
     *
     * @param Recipient $copy
     *
     * @return Notification
     */
    public function removeCopy(Recipient $copy)
    {
        if ($this->copies->contains($copy)) {
            $this->copies->removeElement($copy);
        }

        return $this;
    }

    /**
     * Returns the extra copies.
     *
     * @return ArrayCollection|Recipient
     */
    public function getExtraCopies()
    {
        return $this->extraCopies;
    }

    /**
     * Adds the extra copy.
     *
     * @param Recipient $copy
     *
     * @return Notification
     */
    public function addExtraCopy(Recipient $copy)
    {
        if (!$this->extraCopies->contains($copy)) {
            $this->extraCopies->add($copy);
        }

        return $this;
    }

    /**
     * Removes the extra copy.
     *
     * @param Recipient $copy
     *
     * @return Notification
     */
    public function removeExtraCopy(Recipient $copy)
    {
        if ($this->extraCopies->contains($copy)) {
            $this->extraCopies->removeElement($copy);
        }

        return $this;
    }

    /**
     * Returns the invoices.
     *
     * @return array|InvoiceInterface[]
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Adds the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return Notification
     */
    public function addInvoice(InvoiceInterface $invoice)
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
        }

        return $this;
    }

    /**
     * Removes the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return Notification
     */
    public function removeInvoice(InvoiceInterface $invoice)
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
        }

        return $this;
    }

    /**
     * Returns the attachments.
     *
     * @return array|AttachmentInterface[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Adds the attachment.
     *
     * @param AttachmentInterface $attachment
     *
     * @return Notification
     */
    public function addAttachment(AttachmentInterface $attachment)
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
        }

        return $this;
    }

    /**
     * Removes the attachment.
     *
     * @param AttachmentInterface $attachment
     *
     * @return Notification
     */
    public function removeAttachment(AttachmentInterface $attachment)
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
        }

        return $this;
    }

    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param string $subject
     *
     * @return Notification
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns the payment message.
     *
     * @return PaymentMessage
     */
    public function getPaymentMessage()
    {
        return $this->paymentMessage;
    }

    /**
     * Sets the payment message.
     *
     * @param PaymentMessage $paymentMessage
     *
     * @return Notification
     */
    public function setPaymentMessage(PaymentMessage $paymentMessage = null)
    {
        $this->paymentMessage = $paymentMessage;

        return $this;
    }

    /**
     * Returns the shipment message.
     *
     * @return ShipmentMessage
     */
    public function getShipmentMessage()
    {
        return $this->shipmentMessage;
    }

    /**
     * Sets the shipment message.
     *
     * @param ShipmentMessage $message
     *
     * @return Notification
     */
    public function setShipmentMessage(ShipmentMessage $message = null)
    {
        $this->shipmentMessage = $message;

        return $this;
    }

    /**
     * Returns the custom message.
     *
     * @return string
     */
    public function getCustomMessage()
    {
        return $this->customMessage;
    }

    /**
     * Sets the custom message.
     *
     * @param string $customMessage
     *
     * @return Notification
     */
    public function setCustomMessage($customMessage)
    {
        $this->customMessage = $customMessage;

        return $this;
    }

    /**
     * Returns the include view.
     *
     * @return string
     */
    public function getIncludeView()
    {
        return $this->includeView;
    }

    /**
     * Sets the include view.
     *
     * @param string $includeView
     *
     * @return Notification
     */
    public function setIncludeView($includeView)
    {
        $this->includeView = $includeView;

        return $this;
    }
}
