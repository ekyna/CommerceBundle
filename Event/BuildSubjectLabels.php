<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SubjectLabelEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BuildSubjectLabels extends Event
{
    /**
     * @var array<int, SubjectLabel>
     */
    private array $labels = [];

    public function __construct(
        public readonly string $format,
        public readonly array $parameters
    ) {
    }

    public function addLabel(SubjectLabel $label): void
    {
        $this->labels[] = $label;
    }

    /**
     * @return array<int, SubjectLabel>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }
}
