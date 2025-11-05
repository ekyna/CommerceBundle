<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderSubmit
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderSubmit
{
    /**
     * @var array<int, string>
     */
    private array  $emails     = [];
    private string $subject    = '';
    private string $message    = '';
    private bool   $confirm    = false;
    private bool   $sendEmail  = true;
    private bool   $sendLabels = false;

    public function __construct(
        private readonly SupplierOrderInterface $order
    ) {
    }

    public function getOrder(): SupplierOrderInterface
    {
        return $this->order;
    }

    /**
     * Returns the recipients emails.
     *
     * @return array<int, string>
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * Sets the recipients emails.
     *
     * @param array<int, string> $emails
     */
    public function setEmails(array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Returns the subject.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns the message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Sets the message.
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns user confirmation.
     */
    public function isConfirm(): bool
    {
        return $this->confirm;
    }

    /**
     * Sets user confirmation.
     */
    public function setConfirm(bool $confirm): self
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * Returns whether to send the email.
     */
    public function isSendEmail(): bool
    {
        return $this->sendEmail;
    }

    /**
     * Sets whether to send the email.
     */
    public function setSendEmail(bool $send): self
    {
        $this->sendEmail = $send;

        return $this;
    }

    /**
     * Returns whether to send the labels.
     */
    public function isSendLabels(): bool
    {
        return $this->sendLabels;
    }

    /**
     * Sets whether to send the labels.
     */
    public function setSendLabels(bool $send): self
    {
        $this->sendLabels = $send;

        return $this;
    }
}
