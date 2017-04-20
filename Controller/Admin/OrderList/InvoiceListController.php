<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

/**
 * Class InvoiceListController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceListController extends AbstractListController
{
    protected static string $resource = 'ekyna_commerce.order_invoice';
    protected static string $template = '@EkynaCommerce/Admin/OrderList/invoice.html.twig';
}
