<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class SubjectChoice
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectChoice
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var object
     */
    private $choice;


    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return SubjectChoice
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the choice.
     *
     * @return object
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Sets the choice.
     *
     * @param object $choice
     *
     * @return SubjectChoice
     */
    public function setChoice($choice)
    {
        $this->choice = $choice;

        return $this;
    }
}
