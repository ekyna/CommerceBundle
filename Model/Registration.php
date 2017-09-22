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
     * Constructor.
     *
     * @param CustomerInterface $customer
     */
    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Returns the customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer()
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
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Returns the apply group.
     *
     * @return CustomerGroupInterface
     */
    public function getApplyGroup()
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
    public function setApplyGroup(CustomerGroupInterface $group = null)
    {
        $this->applyGroup = $group;

        return $this;
    }

    /**
     * Returns the invoice address.
     *
     * @return CustomerAddressInterface
     */
    public function getInvoiceAddress()
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
    public function setInvoiceAddress(CustomerAddressInterface $invoiceAddress = null)
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * Returns the invoice contact.
     *
     * @return Contact
     */
    public function getInvoiceContact()
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
    public function setInvoiceContact(Contact $contact = null)
    {
        $this->invoiceContact = $contact;

        return $this;
    }

    /**
     * Returns the comment.
     *
     * @return string
     */
    public function getComment()
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
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }
}
