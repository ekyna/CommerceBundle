<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleItemUpdaterInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResolvePriceAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResolvePriceAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use XhrTrait;

    public function __construct(
        private readonly SaleItemUpdaterInterface $saleItemUpdater,
    ) {
    }

    public function __invoke(): Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        // Prevent if non-root item
        if ($item->getParent()) {
            throw new UnexpectedValueException('Expected root sale item.');
        }

        try {
            if ($this->saleItemUpdater->updateNetPriceAndDiscount($item)) {
                $this->getManager()->update($item);
            }
        } catch (IllegalOperationException) {
            // TODO Add error message to sale view
        }

        $this->postSync($item);

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getRootSale());
        }

        return $this->redirectToReferer($this->generateResourcePath($item->getRootSale()));
    }

    protected function postSync(SaleItemInterface $item): void
    {
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_resolve_price',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_resolve_price',
                'path'     => '/resolve-price',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'sale.button.item.resolve_price',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'fa fa-euro',
            ],
        ];
    }
}
