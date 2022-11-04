<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class IndexController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IndexController implements ControllerInterface
{
    public function __construct(
        private readonly OrderResourceHelper $resourceHelper,
        private readonly Environment         $twig,
    ) {
    }

    public function __invoke(): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $orders = $this->resourceHelper->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/index.html.twig', [
            'customer' => $customer,
            'orders'   => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }
}
