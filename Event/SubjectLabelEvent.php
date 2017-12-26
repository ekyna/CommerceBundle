<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SubjectLabelEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectLabelEvent extends Event
{
    const BUILD = 'ekyna_commerce.subject.label.build';

    /**
     * @var SubjectLabel[]
     */
    private $labels;


    /**
     * Constructor.
     *
     * @param SubjectLabel[] $labels
     */
    public function __construct(array $labels = [])
    {
        $this->labels = [];

        foreach ($labels as $label) {
            $this->addLabel($label);
        }
    }

    /**
     * Adds the subject label.
     *
     * @param SubjectLabel $label
     */
    public function addLabel(SubjectLabel $label)
    {
        $this->labels[] = $label;
    }

    /**
     * Returns the subject labels.
     *
     * @return SubjectLabel[]
     */
    public function getLabels()
    {
        return $this->labels;
    }
}
