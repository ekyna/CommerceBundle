<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class NewsletterSubscription
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterSubscription
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var string
     */
    private $email;

    /**
     * @var array
     */
    private $audiences = [];


    /**
     * Returns the customer key.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * Sets the customer key.
     *
     * @param CustomerInterface $customer
     *
     * @return NewsletterSubscription
     */
    public function setCustomer(CustomerInterface $customer = null): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return NewsletterSubscription
     */
    public function setEmail(string $email = null): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the audiences.
     *
     * @return array
     */
    public function getAudiences(): array
    {
        return $this->audiences;
    }

    /**
     * Sets the audiences.
     *
     * @param array $audiences
     *
     * @return NewsletterSubscription
     */
    public function setAudiences(array $audiences): self
    {
        $this->audiences = $audiences;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'customer'  => $this->customer ? $this->customer->getKey() : null,
            'email'     => $this->customer ? $this->customer->getEmail() : $this->email,
            'audiences' => $this->audiences,
        ];
    }
}
