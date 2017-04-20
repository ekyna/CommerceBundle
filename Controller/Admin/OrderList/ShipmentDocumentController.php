<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

/**
 * Class ShipmentDocumentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentDocumentController extends AbstractDocumentController
{
    protected static string $resource = 'ekyna_commerce.order_shipment';
}
