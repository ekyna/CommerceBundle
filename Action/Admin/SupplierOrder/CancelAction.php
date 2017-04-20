<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CancelAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CancelAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use HelperTrait;
    use FlashTrait;

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (SupplierOrderStates::isCancelableState($resource)) {
            $resource->setState(SupplierOrderStates::STATE_CANCELED);

            //$dispatcher = $this->get(ResourceEventDispatcherInterface::class);
            //$event = $dispatcher->createResourceEvent($resource);
            //$dispatcher->dispatch($event, SupplierOrderEvents::PRE_SUBMIT);

            $event = $this->getManager()->update($resource);

            $this->addFlashFromEvent($event);
        }

        return $this->redirectToReferer($this->generateResourcePath($resource));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_cancel',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_cancel',
                'path'     => '/cancel',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.cancel',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'warning',
                'icon'         => 'remove',
            ],
        ];
    }
}
