<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentPageBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'document_type_choices',
                [DocumentTypes::class, 'getChoices']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'document_design',
                [DocumentHelper::class, 'getDocumentDesign']
            ),
            new TwigFilter(
                'document_notices',
                [DocumentHelper::class, 'getDocumentNotices']
            ),
            new TwigFilter(
                'document_pages',
                [DocumentPageBuilder::class, 'buildDocumentPages']
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
