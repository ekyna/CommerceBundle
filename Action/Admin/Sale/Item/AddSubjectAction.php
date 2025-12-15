<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Component\Commerce\Common\Context\ContextProvider;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AddSubjectAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddSubjectAction extends RA\AbstractAction implements AdminActionInterface
{
    use RA\ManagerTrait;
    use XhrTrait;

    public function __construct(
        private readonly ContextProvider        $contextProvider,
        private readonly FactoryHelperInterface $factoryHelper,
        private readonly SubjectHelperInterface $subjectHelper,
        private readonly SaleItemHelper         $saleItemHelper,
    ) {
    }

    public function __invoke(): Response
    {
        $sale = $this->context->getParentResource();
        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        $this->contextProvider->setContext($sale); // TODO remove ?

        $item = $this->factoryHelper->createItemForSale($sale);

        $subject = $this->subjectHelper->find(
            $this->request->request->get('provider'),
            $this->request->request->getInt('identifier')
        );
        if (null === $subject) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $sale->addItem($item);

        $this->saleItemHelper->initialize($item, $subject);
        $this->saleItemHelper->build($item);

        $event = $this->getManager($sale)->save($sale);

        // TODO Error handling

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_add_subject',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_add_subject',
                'path'    => '/add-subject',
                'methods' => ['POST'],
            ],
        ];
    }
}
