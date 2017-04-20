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
use Ekyna\Component\Commerce\Supplier\Updater\SupplierOrderUpdaterInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RecalculateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RecalculateAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use HelperTrait;
    use FlashTrait;

    private SupplierOrderUpdaterInterface $supplierOrderUpdater;

    public function __construct(SupplierOrderUpdaterInterface $supplierOrderUpdater)
    {
        $this->supplierOrderUpdater = $supplierOrderUpdater;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof SupplierOrderInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($resource->getState() === SupplierOrderStates::STATE_COMPLETED) {
            return $this->redirectToReferer($this->generateResourcePath($resource));
        }

        if ($this->supplierOrderUpdater->updateTotals($resource)) {
            $event = $this->getManager()->update($resource);

            $this->addFlashFromEvent($event);
        }

        return $this->redirectToReferer($this->generateResourcePath($resource));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_supplier_order_recalculate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_recalculate',
                'path'     => '/recalculate',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.recalculate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'warning',
                'icon'         => 'refresh',
            ],
        ];
    }
}
