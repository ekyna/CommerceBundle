<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SyncSubjectAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SyncSubjectAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use XhrTrait;

    private SubjectHelperInterface $subjectHelper;

    public function __construct(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function __invoke(): Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        if ($this->syncItem($item)) {
            $this->getManager()->update($item);
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getSale());
        }

        return $this->redirectToReferer($this->generateResourcePath($item->getSale()));
    }

    /**
     * Syncs the item with it's subject recursively.
     */
    private function syncItem(SaleItemInterface $item): bool
    {
        $changed = $this->subjectHelper->sync($item);

        foreach ($item->getChildren() as $child) {
            $changed = $this->syncItem($child) || $changed;
        }

        return $changed;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_sync_subject',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_sync_subject',
                'path'     => '/sync-subject',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'sale.button.item.sync_subject',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'fa fa-cube',
            ],
        ];
    }
}