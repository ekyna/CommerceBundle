<?php

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
     * Returns the order.
     *
     * @return SupplierOrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the order.
     *
     * @param SupplierOrderInterface $order
     *
     * @return SupplierOrderSubmit
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
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
}

