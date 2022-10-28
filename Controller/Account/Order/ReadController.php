<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order;

use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class ReadController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReadController
{
    public function __construct(
        private readonly OrderResourceHelper $resourceHelper,
        private readonly Environment         $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $orders = $this->resourceHelper->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/read.html.twig', [
            'customer'     => $customer,
            'order'        => $order,
            'orders'       => $orders,
            'route_prefix' => 'ekyna_commerce_account_order',
        ]);

        return (new Response($content))->setPrivate();
    }
}
