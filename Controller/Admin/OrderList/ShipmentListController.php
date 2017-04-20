<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

/**
 * Class ShipmentListController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShipmentListController extends AbstractListController
{
    protected static string $resource = 'ekyna_commerce.order_shipment';
    protected static string $template = '@EkynaCommerce/Admin/OrderList/shipment.html.twig';
}
