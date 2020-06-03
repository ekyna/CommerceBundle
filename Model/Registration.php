<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class Registration
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Registration
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var CustomerGroupInterface
     */
    private $applyGroup;

    /**
     * @var CustomerAddressInterface
     */
    private $invoiceAddress;

    /**
     * @var Contact
     */
    private $invoiceContact;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var bool
     */
    private $newsletter = false;


    /**
     * Constructor.
     *
     * @param CustomerInterface $customer
     */
    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
        $this->applyGroup = $customer->getCustomerGroup();
    }

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * Sets the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return Registration
     */
    public function setCustomer(CustomerInterface $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Returns the apply group.
     *
     * @return CustomerGroupInterface|null
     */
    public function getApplyGroup(): ?CustomerGroupInterface
    {
        return $this->applyGroup;
    }

    /**
     * Sets the apply group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return Registration
     */
    public function setApplyGroup(CustomerGroupInterface $group = null): self
    {
        $this->applyGroup = $group;

        return $this;
    }

    /**
     * Returns the invoice address.
     *
     * @return CustomerAddressInterface|null
     */
    public function getInvoiceAddress(): ?CustomerAddressInterface
    {
        return $this->invoiceAddress;
    }

    /**
     * Sets the invoice address.
     *
     * @param CustomerAddressInterface $invoiceAddress
     *
     * @return Registration
     */
    public function setInvoiceAddress(CustomerAddressInterface $invoiceAddress = null): self
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * Returns the invoice contact.
     *
     * @return Contact|null
     */
    public function getInvoiceContact(): ?Contact
    {
        return $this->invoiceContact;
    }

    /**
     * Sets the invoice contact.
     *
     * @param Contact $contact
     *
     * @return Registration
     */
    public function setInvoiceContact(Contact $contact = null): self
    {
        $this->invoiceContact = $contact;

        return $this;
    }

    /**
     * Returns the comment.
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Sets the comment.
     *
     * @param string $comment
     *
     * @return Registration
     */
    public function setComment(string $comment = null): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Returns the newsletter.
     *
     * @return bool
     */
    public function isNewsletter(): bool
    {
        return $this->newsletter;
    }

    /**
     * Sets the newsletter.
     *
     * @param bool $newsletter
     *
     * @return Registration
     */
    public function setNewsletter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }
}
