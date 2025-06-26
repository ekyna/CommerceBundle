<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class DocumentEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DocumentExtraEvent extends Event
{
    private array $paths = [];

    /**
     * Constructor.
     */
    public function __construct(
        private readonly object $subject,
    ) {
    }

    public function getSubject(): object
    {
        return $this->subject;
    }

    public function addPath(string $path): void
    {
        $this->paths[] = $path;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }
}
