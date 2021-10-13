<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Model\DocumentDesign;
use Ekyna\Bundle\CommerceBundle\Service\Common\CommonRenderer;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Common\Model\MentionSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use League\Flysystem\Filesystem;
use OzdemirBurak\Iris\Color\Hex;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_merge;

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
     * @var CommonRenderer
     */
    private $commonRenderer;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

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
     * @param CommonRenderer           $commonRenderer
     * @param TaxResolverInterface     $taxResolver
     * @param SubjectHelperInterface   $subjectHelper
     * @param array                    $config
     * @param string                   $defaultLocale
     */
    public function __construct(
        SettingsManagerInterface $settings,
        Filesystem $fileSystem,
        UrlGeneratorInterface $urlGenerator,
        CommonRenderer $commonRenderer,
        TaxResolverInterface $taxResolver,
        SubjectHelperInterface $subjectHelper,
        array $config,
        string $defaultLocale
    ) {
        $this->settings       = $settings;
        $this->fileSystem     = $fileSystem;
        $this->urlGenerator   = $urlGenerator;
        $this->commonRenderer = $commonRenderer;
        $this->taxResolver    = $taxResolver;
        $this->subjectHelper  = $subjectHelper;
        $this->config         = $config;
        $this->defaultLocale  = $defaultLocale;
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
     * Returns the document mentions.
     *
     * @param DocumentInterface $document
     *
     * @return string[]
     */
    public function getDocumentMentions(DocumentInterface $document): array
    {
        $sale = $document->getSale();
        $type = $document->getType();
        $locale = $document->getLocale();

        $mentions = $this->getSaleMentions(
            $document->getSale(), $document->getType(), $document->getLocale()
        );

        if ($rule = $document->getTaxRule() ?: $this->taxResolver->resolveSaleTaxRule($sale)) {
            $mentions = array_merge($mentions, $this->getMentions($rule, $type, $locale));
        }

        foreach ($document->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
            $list = $this->getSaleItemMentions($line->getSaleItem(), $document->getType(), $document->getLocale());

            if (empty($list)) {
                continue;
            }

            $list = array_map(function($html) {
                return strip_tags(strtr($html, ['</p>' => '<br>']), '<br><a><span><em><strong>');
            }, $list);

            $mentions[] = sprintf('<p><strong>%s</strong> : %s</p>', $line->getDesignation(), implode(' ', $list));
        }

        return $mentions;
    }

    /**
     * Returns the shipment mentions.
     *
     * @param ShipmentInterface $shipment
     *
     * @return array
     */
    public function getShipmentMentions(ShipmentInterface $shipment): array
    {
        $type = DocumentTypes::TYPE_SHIPMENT_BILL;
        $locale = $shipment->getSale()->getLocale();
        $sale = $shipment->getSale();

        $mentions = $this->getSaleMentions($shipment->getSale(), $type, $locale);

        if ($rule = $this->taxResolver->resolveSaleTaxRule($sale)) {
            $mentions = array_merge($mentions, $this->getMentions($rule, $type, $locale));
        }

        foreach ($shipment->getItems() as $item) {
            $list = $this->getSaleItemMentions($item->getSaleItem(), $type, $locale);

            if (empty($list)) {
                continue;
            }

            $mentions[] = sprintf(
                '<p><strong>%s</strong> : %s</p>',
                $item->getSaleItem()->getDesignation(),
                implode(' ', $list)
            );
        }

        return $mentions;
    }

    /**
     * Returns the sale's mentions.
     *
     * @param SaleInterface $sale
     * @param string        $type
     * @param string|null   $locale
     *
     * @return array
     */
    public function getSaleMentions(SaleInterface $sale, string $type, string $locale = null): array
    {
        if (!$method = $sale->getPaymentMethod()) {
            return [];
        }

        return $this->getMentions($method, $type, $locale);
    }

    /**
     * Returns the sale item's mentions.
     *
     * @param SaleItemInterface $item
     * @param string            $type
     * @param string|null       $locale
     *
     * @return array
     */
    public function getSaleItemMentions(SaleItemInterface $item, string $type, string $locale = null): array
    {
        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return [];
        }

        if (!$subject instanceof MentionSubjectInterface) {
            return [];
        }

        return $this->getMentions($subject, $type, $locale);
    }

    /**
     * Returns the mentions.
     *
     * @param MentionSubjectInterface $subject
     * @param string                  $type
     * @param string|null             $locale
     *
     * @return array
     */
    public function getMentions(MentionSubjectInterface $subject, string $type, string $locale = null): array
    {
        $list = [];
        foreach ($subject->getMentions() as $mention) {
            if (!in_array($type, $mention->getDocumentTypes(), true)) {
                continue;
            }

            if (empty($content = $mention->translate($locale)->getContent())) {
                continue;
            }

            $list[] = $content;
        }

        return $list;
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

        $design->setHeaderHtml($this->commonRenderer->renderAddress($customer->getDefaultInvoiceAddress(), [
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
