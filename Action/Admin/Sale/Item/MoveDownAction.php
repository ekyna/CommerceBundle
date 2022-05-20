<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MoveDownAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveDownAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use XhrTrait;

    public function __invoke(): Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        if (!$item->isLast()) {
            $item->setPosition($item->getPosition() + 1);

            $this->getManager()->save($item);
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getRootSale());
        }

        return $this->redirectToReferer($this->generateResourcePath($item->getRootSale()));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_move_down',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_move_down',
                'path'     => '/move-down',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.move_down',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'arrow-down',
            ],
        ];
    }
}
