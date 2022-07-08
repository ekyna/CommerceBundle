<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectLabel
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectLabel
{
    public const FORMAT_SMALL = 'small';
    public const FORMAT_LARGE = 'large';

    private ?string $designation = null;
    private ?string $reference   = null;
    private ?string $barcode     = null;
    private ?string $geocode     = null;
    private ?string $extra       = null;

    public function __construct(private readonly SubjectInterface $subject)
    {
    }

    public function getSubject(): SubjectInterface
    {
        return $this->subject;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): SubjectLabel
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): SubjectLabel
    {
        $this->reference = $reference;

        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): SubjectLabel
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function getGeocode(): ?string
    {
        return $this->geocode;
    }

    public function setGeocode(?string $geocode): SubjectLabel
    {
        $this->geocode = $geocode;

        return $this;
    }

    public function getExtra(): ?string
    {
        return $this->extra;
    }

    public function setExtra(?string $extra): SubjectLabel
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Returns the label formats.
     *
     * @return array<int, string>
     */
    public static function getFormats(): array
    {
        return [
            self::FORMAT_LARGE,
            self::FORMAT_SMALL,
        ];
    }
}
