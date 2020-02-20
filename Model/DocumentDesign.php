<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class DocumentDesign
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentDesign
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $brandName;

    /**
     * @var string
     */
    private $primaryColor;

    /**
     * @var string
     */
    private $secondaryColor;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var string
     */
    private $logoLink;

    /**
     * @var string
     */
    private $headerHtml;

    /**
     * @var string
     */
    private $footerHtml;

    /**
     * @var bool
     */
    private $addLinks = true;


    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return DocumentDesign
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale
     *
     * @return DocumentDesign
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns the brandName.
     *
     * @return string
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * Sets the brandName.
     *
     * @param string $brandName
     *
     * @return DocumentDesign
     */
    public function setBrandName(string $brandName = null): self
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * Returns the brand primary color.
     *
     * @return string
     */
    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    /**
     * Sets the brand primary color.
     *
     * @param string $color
     *
     * @return DocumentDesign
     */
    public function setPrimaryColor(string $color = null): self
    {
        $this->primaryColor = $color;

        return $this;
    }

    /**
     * Returns the brand secondary color.
     *
     * @return string
     */
    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    /**
     * Sets the brand secondary color.
     *
     * @param string $color
     *
     * @return DocumentDesign
     */
    public function setSecondaryColor(string $color = null): self
    {
        $this->secondaryColor = $color;

        return $this;
    }

    /**
     * Returns the logo image url.
     *
     * @return string
     */
    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    /**
     * Sets the logo image url.
     *
     * @param string $url
     *
     * @return DocumentDesign
     */
    public function setLogoPath(string $url = null): self
    {
        $this->logoPath = $url;

        return $this;
    }

    /**
     * Returns the logo link url.
     *
     * @return string
     */
    public function getLogoLink(): ?string
    {
        return $this->logoLink;
    }

    /**
     * Sets the logo link url.
     *
     * @param string $url
     *
     * @return DocumentDesign
     */
    public function setLogoLink(string $url = null): self
    {
        $this->logoLink = $url;

        return $this;
    }

    /**
     * Returns the header html.
     *
     * @return string
     */
    public function getHeaderHtml(): ?string
    {
        return $this->headerHtml;
    }

    /**
     * Sets the header html.
     *
     * @param string $html
     *
     * @return DocumentDesign
     */
    public function setHeaderHtml(string $html = null): self
    {
        $this->headerHtml = $html;

        return $this;
    }

    /**
     * Returns the footer html.
     *
     * @return string
     */
    public function getFooterHtml(): ?string
    {
        return $this->footerHtml;
    }

    /**
     * Sets the footer html.
     *
     * @param string $html
     *
     * @return DocumentDesign
     */
    public function setFooterHtml(string $html = null): self
    {
        $this->footerHtml = $html;

        return $this;
    }

    /**
     * Returns whether to add links.
     *
     * @return bool
     */
    public function isAddLinks(): bool
    {
        return $this->addLinks;
    }

    /**
     * Sets whether to add links.
     *
     * @param bool $add
     *
     * @return DocumentDesign
     */
    public function setAddLinks(bool $add): DocumentDesign
    {
        $this->addLinks = $add;

        return $this;
    }
}
