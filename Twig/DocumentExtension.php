<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentPageBuilder;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class DocumentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentExtension extends AbstractExtension
{
    /**
     * @var SettingsManagerInterface
     */
    private $settings;


    /**
     * Constructor.
     *
     * @param SettingsManagerInterface $settings
     */
    public function __construct(SettingsManagerInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'document_pages',
                [DocumentPageBuilder::class, 'buildDocumentPages'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'document_footer',
                [$this, 'getDocumentFooter'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'shipment_pages',
                [DocumentPageBuilder::class, 'buildShipmentPages'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'shipment_remaining_pages',
                [DocumentPageBuilder::class, 'buildShipmentRemainingPages'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns the document footer.
     *
     * @param DocumentInterface $document
     *
     * @return string
     */
    public function getDocumentFooter(DocumentInterface $document): string
    {
        $locale = $document->getLocale();
        $sale = $document->getSale();

        if ($method = $sale->getPaymentMethod()) {
            $translation = $method->translate($locale);

            if (!empty($footer = $translation->getFooter())) {
                return $footer;
            }
        }

        return $this->settings->getParameter('commerce.invoice_footer', $locale);
    }
}
