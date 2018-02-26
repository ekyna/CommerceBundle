<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;

/**
 * Class SupplierOrderSubmit
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderSubmit
{
    /**
     * @var SupplierOrderInterface
     */
    private $order;

    /**
     * @var string[]
     */
    private $emails;

    /**
     * @var string
     */
    private $message;

    /**
     * @var bool
     */
    private $confirm = false;

    /**
     * @var bool
     */
    private $sendEmail = true;

    /**
     * @var bool
     */
    private $sendLabels = false;


    /**
     * Constructor.
     *
     * @param SupplierOrderInterface $order
     */
    public function __construct(SupplierOrderInterface $order)
    {
        $this->order = $order;

        // For validation
        if (in_array($this->order->getState(), [SupplierOrderStates::STATE_NEW, SupplierOrderStates::STATE_CANCELED], true)) {
            $this->order
                ->setState(SupplierOrderStates::STATE_ORDERED)
                ->setOrderedAt(new \DateTime());
        }
    }

    /**
     * Returns the order.
     *
     * @return SupplierOrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns the emails.
     *
     * @return string[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Sets the emails.
     *
     * @param string[] $emails
     *
     * @return SupplierOrderSubmit
     */
    public function setEmails(array $emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message.
     *
     * @param string $message
     *
     * @return SupplierOrderSubmit
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns the confirm.
     *
     * @return bool
     */
    public function isConfirm()
    {
        return $this->confirm;
    }

    /**
     * Sets the confirm.
     *
     * @param bool $confirm
     *
     * @return SupplierOrderSubmit
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * Returns whether to send the email.
     *
     * @return bool
     */
    public function isSendEmail()
    {
        return $this->sendEmail;
    }

    /**
     * Sets whether to send the email.
     *
     * @param bool $send
     *
     * @return SupplierOrderSubmit
     */
    public function setSendEmail($send)
    {
        $this->sendEmail = (bool)$send;

        return $this;
    }

    /**
     * Returns whether to send the labels.
     *
     * @return bool
     */
    public function isSendLabels()
    {
        return $this->sendLabels;
    }

    /**
     * Sets whether to send the labels.
     *
     * @param bool $send
     *
     * @return SupplierOrderSubmit
     */
    public function setSendLabels($send)
    {
        $this->sendLabels = (bool)$send;

        return $this;
    }
}

