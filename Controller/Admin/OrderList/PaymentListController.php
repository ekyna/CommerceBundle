<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList;

/**
 * Class PaymentListController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderList
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PaymentListController extends AbstractListController
{
    protected static string $resource = 'ekyna_commerce.order_payment';
    protected static string $template = '@EkynaCommerce/Admin/OrderList/payment.html.twig';
}
