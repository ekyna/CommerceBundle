<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentLinesHelper;
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
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'document_type_choices',
                [DocumentTypes::class, 'getChoices']
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'document_design',
                [DocumentHelper::class, 'getDocumentDesign']
            ),
            new TwigFilter(
                'document_mentions',
                [DocumentHelper::class, 'getDocumentMentions']
            ),
            new TwigFilter(
                'shipment_mentions',
                [DocumentHelper::class, 'getShipmentMentions']
            ),
            new TwigFilter(
                'document_lines',
                [DocumentLinesHelper::class, 'buildDocumentLines']
            ),
            new TwigFilter(
                'shipment_lines',
                [DocumentLinesHelper::class, 'buildShipmentLines']
            ),
            new TwigFilter(
                'shipment_remaining_lines',
                [DocumentLinesHelper::class, 'buildShipmentRemainingLines']
            ),
        ];
    }
}
