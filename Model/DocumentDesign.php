<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class DocumentDesign
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentDesign
{
    private string  $type;
    private string  $locale;
    private ?string $brandName      = null;
    private ?string $primaryColor   = null;
    private ?string $secondaryColor = null;
    private ?string $logoPath       = null;
    private ?string $logoLink       = null;
    private ?string $headerHtml     = null;
    private ?string $footerHtml     = null;
    private bool    $addLinks       = true;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    public function setBrandName(?string $brandName): self
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * Returns the brand primary color.
     */
    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    /**
     * Sets the brand primary color.
     */
    public function setPrimaryColor(?string $color): self
    {
        $this->primaryColor = $color;

        return $this;
    }

    /**
     * Returns the brand secondary color.
     */
    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    /**
     * Sets the brand secondary color.
     */
    public function setSecondaryColor(?string $color): self
    {
        $this->secondaryColor = $color;

        return $this;
    }

    /**
     * Returns the logo image url.
     */
    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    /**
     * Sets the logo image url.
     */
    public function setLogoPath(?string $url): self
    {
        $this->logoPath = $url;

        return $this;
    }

    /**
     * Returns the logo link url.
     */
    public function getLogoLink(): ?string
    {
        return $this->logoLink;
    }

    /**
     * Sets the logo link url.
     */
    public function setLogoLink(?string $url): self
    {
        $this->logoLink = $url;

        return $this;
    }

    public function getHeaderHtml(): ?string
    {
        return $this->headerHtml;
    }

    public function setHeaderHtml(?string $html): self
    {
        $this->headerHtml = $html;

        return $this;
    }

    public function getFooterHtml(): ?string
    {
        return $this->footerHtml;
    }

    public function setFooterHtml(?string $html): self
    {
        $this->footerHtml = $html;

        return $this;
    }

    /**
     * Returns whether to add links.
     */
    public function isAddLinks(): bool
    {
        return $this->addLinks;
    }

    /**
     * Sets whether to add links.
     */
    public function setAddLinks(bool $add): DocumentDesign
    {
        $this->addLinks = $add;

        return $this;
    }
}
