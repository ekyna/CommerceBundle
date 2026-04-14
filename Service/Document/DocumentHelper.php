<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Bundle\CommerceBundle\Event\DocumentDesignEvent;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentDesign;
use Ekyna\Bundle\CommerceBundle\Service\Common\CommonRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Common\Model\MentionSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Document\Event\SaleItemMentionEvent;
use Ekyna\Component\Commerce\Document\Event\SaleMentionEvent;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use OzdemirBurak\Iris\Color\Hex;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_merge;
use function array_push;
use function implode;
use function in_array;
use function sprintf;
use function strip_tags;
use function strtr;

/**
 * Class DocumentHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentHelper
{
    /** @var array<string, DocumentDesign> */
    private array $defaultDesigns = [];

    public function __construct(
        private readonly SettingManagerInterface  $settings,
        private readonly Filesystem               $fileSystem,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly CommonRenderer           $commonRenderer,
        private readonly TaxResolverInterface     $taxResolver,
        private readonly SubjectHelperInterface   $subjectHelper,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DocumentAttributeHelper  $localeHelper,
        private readonly array                    $config
    ) {
    }

    /**
     * Builds the document design.
     *
     * @param object      $document The document
     * @param string|null $type     The document type
     *
     * @return DocumentDesign
     */
    public function getDocumentDesign(object $document, string $type = null): DocumentDesign
    {
        $design = clone $this->getDefaultDesign($document);
        $design->setType($this->localeHelper->getType($document, $type));

        if ($sale = $this->localeHelper->getSale($document)) {
            $this->fillFromSale($design, $sale);
        }

        if ($document instanceof DocumentInterface) {
            $this->eventDispatcher->dispatch(new DocumentDesignEvent($document, $design));
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

        $mentions = $this->getSaleMentions($sale, $type, $locale);

        if ($rule = $document->getTaxRule() ?: $this->taxResolver->resolveSaleTaxRule($sale)) {
            $mentions = array_merge($mentions, $this->getMentions($rule, $type, $locale));
        }

        foreach ($document->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
            $list = $this->getSaleItemMentions($line->getSaleItem(), $type, $locale);

            if (empty($list)) {
                continue;
            }

            $list = array_map(function ($html) {
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

        $mentions = $this->getSaleMentions($sale, $type, $locale);

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

    public function getDocumentIncoterm(DocumentInterface $document): ?string
    {
        if (null === $incoterm = $document->getIncoterm()) {
            return null;
        }

        $address = $this->getDocumentDeliveryAddress($document);

        $address = $this->commonRenderer->renderAddress($address, ['inline' => true]);

        $parts = ["INCOTERM: $incoterm $address"];

        if (!empty($address = $this->getDocumentDestinationAddress($document))) {
            $address = $this->commonRenderer->renderAddress($address, ['inline' => true]);

            $parts[] = "Final destination: $address";
        }

        $parts[] = 'Incoterms® 2020';

        return implode('<br>', $parts);
    }

    public function getDocumentInvoiceAddress(DocumentInterface $document): ?array
    {
        if ($document instanceof InvoiceInterface && !empty($address = $document->getCustomInvoiceAddress())) {
            return $address;
        }

        return $document->getInvoiceAddress();
    }

    public function getDocumentDeliveryAddress(DocumentInterface $document): ?array
    {
        if ($document instanceof InvoiceInterface && !empty($address = $document->getCustomDeliveryAddress())) {
            return $address;
        }

        if (!empty($address = $document->getRelayPoint())) {
            return $address;
        }

        if (!empty($address = $document->getDeliveryAddress())) {
            return $address;
        }

        return $document->getInvoiceAddress();
    }

    public function getDocumentDestinationAddress(DocumentInterface $document): ?array
    {
        if ($document instanceof InvoiceInterface && !empty($address = $document->getCustomDestinationAddress())) {
            return $address;
        }

        return $document->getDestinationAddress();
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
        $this->eventDispatcher->dispatch($event = new SaleMentionEvent($sale, $type, $locale));

        $mentions = $event->getMentions();

        if (null === $method = $sale->getPaymentMethod()) {
            return $mentions;
        }

        array_push($mentions, ...$this->getMentions($method, $type, $locale));

        return $mentions;
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
        $this->eventDispatcher->dispatch($event = new SaleItemMentionEvent($item, $type, $locale));

        $mentions = $event->getMentions();

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return $mentions;
        }

        if (!$subject instanceof MentionSubjectInterface) {
            return $mentions;
        }

        array_push($mentions, ...$this->getMentions($subject, $type, $locale));

        return $mentions;
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
        $locale = $this->localeHelper->getLocale($document);

        if (isset($this->defaultDesigns[$locale])) {
            return $this->defaultDesigns[$locale];
        }

        $logoPath = $this->config['logo_path'];
        if (!str_starts_with($logoPath, '/')) {
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

        /** @var CustomerInterface $customer */
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

        try {
            if (!$this->fileSystem->fileExists($logo->getPath())) {
                return;
            }
        } catch (FilesystemException) {
            return;
        }

        $design
            ->setBrandName($customer->getCompany())
            ->setPrimaryColor($color = $customer->getBrandColor())
            ->setSecondaryColor($color ? (string)(new Hex($color))->toHsl()->lightness(90) : null)
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
}
