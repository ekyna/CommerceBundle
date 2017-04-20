<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

/**
 * Class InvoiceDocumentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceDocumentController extends AbstractDocumentController
{
    protected static string $resource = 'ekyna_commerce.order_invoice';
}
