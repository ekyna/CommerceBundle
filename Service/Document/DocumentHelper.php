<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Model\DocumentDesign;
use Ekyna\Bundle\CommerceBundle\Service\Common\AddressRenderer;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use League\Flysystem\Filesystem;
use OzdemirBurak\Iris\Color\Hex;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class DocumentHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentHelper
{
    /**
     * @var SettingsManagerInterface
     */
    private $settings;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var AddressRenderer
     */
    private $addressRenderer;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var [<locale> => DocumentDesign]
     */
    private $defaultDesigns = [];


    /**
     * Constructor.
     *
     * @param SettingsManagerInterface $settings
     * @param Filesystem               $fileSystem
     * @param UrlGeneratorInterface    $urlGenerator
     * @param AddressRenderer          $addressRenderer
     * @param array                    $config
     * @param string                   $defaultLocale
     */
    public function __construct(
        SettingsManagerInterface $settings,
        Filesystem $fileSystem,
        UrlGeneratorInterface $urlGenerator,
        AddressRenderer $addressRenderer,
        array $config,
        string $defaultLocale
    ) {
        $this->settings        = $settings;
        $this->fileSystem      = $fileSystem;
        $this->urlGenerator    = $urlGenerator;
        $this->addressRenderer = $addressRenderer;
        $this->config          = $config;
        $this->defaultLocale   = $defaultLocale;
    }

    /**
     * Builds the document design.
     *
     * @param object $document The document
     * @param string $type     The document type
     *
     * @return DocumentDesign
     */
    public function getDocumentDesign(object $document, string $type = null): DocumentDesign
    {
        $design = clone $this->getDefaultDesign($document);
        $design->setType($this->getDocumentType($document, $type));

        if ($sale = $this->getSale($document)) {
            $this->fillFromSale($design, $sale);
        }

        return $design;
    }

    /**
     * Returns the localized default design for the given document.
     *
     * @param object $document
     *
     * @return DocumentDesign
     */
    protected function getDefaultDesign(object $document): DocumentDesign
    {
        $locale = $this->getLocale($document);

        if (isset($this->defaultDesigns[$locale])) {
            return $this->defaultDesigns[$locale];
        }

        $logoPath = $this->config['logo_path'];
        if (0 !== strpos($logoPath, '/')) {
            $logoPath = '/' . $logoPath;
        }

        /** @var DocumentDesign $design */
        $design = new $this->config['design_class']();
        $design
            ->setLocale($locale)
            ->setBrandName($this->settings->getParameter('general.site_name'))
            ->setLogoPath($logoPath)
            ->setLogoLink('/')
            ->setPrimaryColor($this->config['primary_color'])
            ->setSecondaryColor($this->config['secondary_color'])
            ->setFooterHtml($this->settings->getParameter('commerce.invoice_footer', $locale));

        return $this->defaultDesigns[$locale] = $design;
    }

    /**
     * Builds the document design from the given sale.
     *
     * @param DocumentDesign $design
     * @param SaleInterface  $sale
     */
    protected function fillFromSale(DocumentDesign $design, SaleInterface $sale): void
    {
        if ($method = $sale->getPaymentMethod()) {
            $translation = $method->translate($design->getLocale());

            if (!empty($html = $translation->getFooter())) {
                $design->setFooterHtml($html);
            }
        }

        if ($customer = $sale->getCustomer()) {
            $this->fillFromCustomer($design, $customer);
        }
    }

    /**
     * Builds the document design from the given customer.
     *
     * @param DocumentDesign    $design
     * @param CustomerInterface $customer
     */
    protected function fillFromCustomer(DocumentDesign $design, CustomerInterface $customer): void
    {
        if ($customer->hasParent()) {
            $customer = $customer->getParent();
        }

        if (!in_array($design->getType(), $customer->getDocumentTypes(), true)) {
            return;
        }

        if (!$logo = $customer->getBrandLogo()) {
            return;
        }

        if (!$this->fileSystem->has($logo->getPath())) {
            return;
        }

        $design
            ->setBrandName($customer->getCompany())
            ->setPrimaryColor($color = $customer->getBrandColor())
            ->setSecondaryColor($color ? (new Hex($color))->toHsl()->lightness(90) : null)
            ->setLogoPath($this->urlGenerator->generate('ekyna_commerce_api_customer_logo', [
                'customerNumber' => $customer->getNumber(),
            ]))
            ->setLogoLink($customer->getBrandUrl())
            ->setAddLinks(false);

        $design->setHeaderHtml($this->addressRenderer->renderAddress($customer->getDefaultInvoiceAddress(), [
            'locale' => $design->getLocale(),
        ]));

        if (!empty($html = $customer->getDocumentFooter())) {
            $design->setFooterHtml($html);
        }
    }

    /**
     * Returns the document type.
     *
     * @param object      $document
     * @param string|null $default
     *
     * @return string|null
     */
    protected function getDocumentType(object $document, string $default = null): ?string
    {
        if ($document instanceof DocumentInterface) {
            return $document->getType();
        }

        return $default;
    }

    /**
     * Returns the document locale.
     *
     * @param object $document
     *
     * @return string|null
     */
    protected function getLocale(object $document): string
    {
        if ($document instanceof DocumentInterface) {
            return $document->getLocale();
        }

        if ($document instanceof ShipmentInterface) {
            return $document->getLocale();
        }

        return $this->defaultLocale;
    }

    /**
     * Returns the document sale.
     *
     * @param object $document
     *
     * @return SaleInterface|null
     */
    protected function getSale(object $document): ?SaleInterface
    {
        if ($document instanceof DocumentInterface) {
            return $document->getSale();
        }

        if ($document instanceof ShipmentInterface) {
            return $document->getSale();
        }

        return null;
    }
}
