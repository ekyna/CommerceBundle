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
    public function __construct($email, $name = null, $type = null)
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
     * Returns the email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * Returns the role.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
