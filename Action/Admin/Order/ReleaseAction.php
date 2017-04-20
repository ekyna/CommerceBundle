<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReleaseAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReleaseAction extends AbstractOrderAction
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$order = $this->getOrder()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($this->toggleReleased($order)) {
            $event = $this->getManager()->save($order);

            $this->addFlashFromEvent($event);
        }

        return $this->redirectToReferer($this->generateResourcePath($order));
    }

    private function toggleReleased(OrderInterface $order): bool
    {
        if ($order->isReleased()) {
            $order->setReleased(false);

            return true;
        }

        if (!$order->canBeReleased()) {
            return false;
        }

        $order->setReleased(true);

        return true;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_order_release',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_release',
                'path'     => '/release',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'order.button.release',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'warning',
                'icon'         => 'ok-circle',
            ],
        ];
    }
}
