<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectOrderExport
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectOrderExport
{
    /**
     * @var Collection|SubjectInterface[]|SubjectIdentity[]
     */
    private $subjects;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->subjects = new ArrayCollection();
    }

    /**
     * Adds the subject.
     *
     * @param SubjectInterface|SubjectIdentity $subject
     *
     * @return SubjectOrderExport
     */
    public function addSubject(object $subject): self
    {
        if (!$subject instanceof SubjectInterface && !$subject instanceof SubjectIdentity) {
            throw new UnexpectedTypeException($subject, [
                SubjectInterface::class,
                SubjectIdentity::class,
            ]);
        }

        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
        }

        return $this;
    }

    /**
     * Removes the subject.
     *
     * @param SubjectInterface|SubjectIdentity $subject
     *
     * @return SubjectOrderExport
     */
    public function removeSubject(object $subject): self
    {
        if (!$subject instanceof SubjectInterface && !$subject instanceof SubjectIdentity) {
            throw new UnexpectedTypeException($subject, [
                SubjectInterface::class,
                SubjectIdentity::class,
            ]);
        }

        if ($this->subjects->contains($subject)) {
            $this->subjects->removeElement($subject);
        }

        return $this;
    }

    /**
     * Returns the subjects.
     *
     * @return Collection|SubjectInterface[]|SubjectIdentity[]
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }
}
