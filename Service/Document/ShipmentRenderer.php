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
     * @inheritDoc
     */
    protected function supports($subject)
    {
        return $subject instanceof ShipmentInterface;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'EkynaCommerceBundle:Document:shipment.html.twig';
    }
}
