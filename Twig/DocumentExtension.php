<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentPageBuilder;
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
}
