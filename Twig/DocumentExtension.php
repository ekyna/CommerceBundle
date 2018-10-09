<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentRowsBuilder;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculator;

/**
 * Class DocumentExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentExtension extends \Twig_Extension
{
    /**
     * @var DocumentRowsBuilder
     */
    private $documentRowsBuilder;

    /**
     * @var ShipmentCalculator
     */
    private $shipmentCalculator;


    /**
     * Constructor.
     *
     * @param DocumentRowsBuilder $documentRowsBuilder
     * @param ShipmentCalculator  $shipmentCalculator
     */
    public function __construct(DocumentRowsBuilder $documentRowsBuilder, ShipmentCalculator $shipmentCalculator)
    {
        $this->documentRowsBuilder = $documentRowsBuilder;
        $this->shipmentCalculator = $shipmentCalculator;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'document_goods_rows',
                [$this->documentRowsBuilder, 'buildGoodRows'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'shipment_remaining_list',
                [$this->shipmentCalculator, 'buildRemainingList'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
