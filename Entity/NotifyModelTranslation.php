<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Component\Resource\Model\AbstractTranslation;

/**
 * Class NotifyModelTranslation
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelTranslation extends AbstractTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $message;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the subject.
     *
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Sets the subject.
     *
     * @param string|null $subject
     *
     * @return NotifyModelTranslation
     */
    public function setSubject(string $subject = null): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Returns the message.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Sets the message.
     *
     * @param string|null $message
     *
     * @return NotifyModelTranslation
     */
    public function setMessage(string $message = null): self
    {
        $this->message = $message;

        return $this;
    }
}
