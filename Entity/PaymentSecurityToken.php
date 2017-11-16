<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Payum\Core\Model\Token;
use Payum\Core\Security\TokenInterface;

/**
 * Class PaymentSecurityToken
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentSecurityToken extends Token
{
    /**
     * @var \DateTime
     */
    protected $createdAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->createdAt = new \DateTime();
    }

    /**
     * Returns the "created at" date.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the "created at" date.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|TokenInterface
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
