<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class Recipient
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Recipient
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;


    /**
     * Constructor.
     *
     * @param string $email
     * @param string $name
     * @param string $type
     */
    public function __construct($email = null, $name = null, $type = null)
    {
        $this->email = $email;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getChoiceLabel();
    }

    /**
     * Returns the choice label.
     *
     * @return string
     */
    public function getChoiceLabel()
    {
        $label = '';

        if (!empty($this->type)) {
            $label = '[' . $this->type . '] ';
        }

        if (!empty($this->name)) {
            $label .= sprintf('%s &lt;%s&gt;', $this->name, $this->email);
        } else {
            $label .= $this->email;
        }

        return $label;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return Recipient
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return Recipient
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return Recipient
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the role.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
