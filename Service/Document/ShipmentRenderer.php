<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class ShipmentRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRenderer extends AbstractRenderer
{
    /**
     * @var ShipmentInterface
     */
    private $shipment;


    /**
     * Constructor.
     *
     * @param ShipmentInterface $shipment
     */
    public function __construct(ShipmentInterface $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * @inheritDoc
     */
    public function getLastModified()
    {
        return $this->shipment->getUpdatedAt();
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return $this->shipment->getNumber();
    }

    /**
     * @inheritdoc
     */
    protected function getContent()
    {
        return $this->renderView('EkynaCommerceBundle:Document:shipment.html.twig', [
            'logo_path' => $this->logoPath,
            'shipment'  => $this->shipment,
        ]);
    }
}
