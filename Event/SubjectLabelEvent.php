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
class SubjectLabelEvent extends Event
{
    public const BUILD = 'ekyna_commerce.subject.label.build';

    /**
     * @var array<SubjectLabel>
     */
    private array $labels;


    /**
     * @param array<SubjectLabel> $labels
     */
    public function __construct(array $labels = [])
    {
        $this->labels = [];

        foreach ($labels as $label) {
            $this->addLabel($label);
        }
    }

    public function addLabel(SubjectLabel $label): void
    {
        $this->labels[] = $label;
    }

    /**
     * @return array<SubjectLabel>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }
}
