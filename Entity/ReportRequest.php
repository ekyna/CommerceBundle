<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use DateTime;
use DateTimeInterface;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Class ReportRequest
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportRequest
{
    private ?int $id;

    private ?UserInterface $user;

    private ?DateTimeInterface $requestedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): ReportRequest
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): ReportRequest
    {
        $this->user = $user;

        return $this;
    }

    public function getRequestedAt(): ?DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(?DateTimeInterface $requestedAt): ReportRequest
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getMinutesPast(): int
    {
        return (int)floor(((new DateTime())->getTimestamp() - $this->getRequestedAt()->getTimestamp()) / 60);
    }
}
