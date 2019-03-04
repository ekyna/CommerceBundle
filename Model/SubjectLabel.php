<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class SubjectLabel
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectLabel
{
    const FORMAT_SMALL = 'small';
    const FORMAT_LARGE = 'large';

    /**
     * @var object
     */
    private $subject;

    /**
     * @var string
     */
    private $designation;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $barcode;

    /**
     * @var string
     */
    private $geocode;

    /**
     * @var string
     */
    private $extra;


    /**
     * Constructor.
     *
     * @param object $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Returns the subject.
     *
     * @return object
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return SubjectLabel
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return SubjectLabel
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Returns the barcode.
     *
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * Sets the barcode.
     *
     * @param string $barcode
     *
     * @return SubjectLabel
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * Sets the geocode.
     *
     * @param string $geocode
     *
     * @return SubjectLabel
     */
    public function setGeocode($geocode)
    {
        $this->geocode = $geocode;

        return $this;
    }

    /**
     * Returns the extra.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Sets the extra.
     *
     * @param string $extra
     *
     * @return SubjectLabel
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }
}
