<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentPageBuilder;

/**
 * Class DocumentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentExtension extends \Twig_Extension
{
    /**
     * @var DocumentPageBuilder
     */
    private $documentPageBuilder;


    /**
     * Constructor.
     *
     * @param DocumentPageBuilder $documentPageBuilder
     */
    public function __construct(DocumentPageBuilder $documentPageBuilder)
    {
        $this->documentPageBuilder = $documentPageBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'document_pages',
                [$this->documentPageBuilder, 'buildDocumentPages'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'shipment_pages',
                [$this->documentPageBuilder, 'buildShipmentPages'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'shipment_remaining_pages',
                [$this->documentPageBuilder, 'buildShipmentRemainingPages'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
