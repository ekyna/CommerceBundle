<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use DateTime;
use DateTimeInterface;
use Payum\Core\Model\Token;
use Payum\Core\Security\TokenInterface;

/**
 * Class PaymentSecurityToken
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentSecurityToken extends Token
{
    protected DateTimeInterface $createdAt;

    public function __construct()
    {
        parent::__construct();

        $this->createdAt = new DateTime();
    }

    /**
     * Returns the "created at" date.
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the "created at" date.
     */
    public function setCreatedAt(DateTimeInterface $createdAt): TokenInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
