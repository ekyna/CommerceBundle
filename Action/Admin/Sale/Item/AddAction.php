<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\AdminBundle\Action\AbstractCreateFlowAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemCreateFlow;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Common\Context\ContextProvider;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class AddAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddAction extends AbstractCreateFlowAction
{
    use XhrTrait;

    private ContextProvider          $contextProvider;
    private FactoryHelperInterface   $factoryHelper;
    private SaleHelper               $saleHelper;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        FormFlowInterface        $flow,
        ContextProvider          $contextProvider,
        FactoryHelperInterface   $factoryHelper,
        SaleHelper               $saleHelper,
        EventDispatcherInterface $eventDispatcher
    ) {
        /** @var SaleItemCreateFlow $flow */
        parent::__construct($flow);

        $this->contextProvider = $contextProvider;
        $this->factoryHelper = $factoryHelper;
        $this->saleHelper = $saleHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function createResource(): ResourceInterface
    {
        $sale = $this->context->getParentResource();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        $this->contextProvider->setContext($sale);

        return $this->factoryHelper->createItemForSale($sale);
    }

    protected function doPersist(): ResourceEventInterface
    {
        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        $sale->addItem($item);

        return $this->getManager($sale)->save($sale);
    }

    protected function onPostPersist(): ?Response
    {
        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    protected function onRenderModal(Modal $modal): ?Response
    {
        $modal
            ->setTitle(t('sale.header.item.add', [], 'EkynaCommerce'))
            ->setButtons([]);

        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        $this->eventDispatcher->dispatch(
            new SaleItemModalEvent($modal, $item),
            SaleItemModalEvent::EVENT_ADD
        );

        return null;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_add',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_add',
                'path'    => '/add',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'sale.button.item.add',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
            'options'    => [
                'template'      => '@EkynaCommerce/Admin/Common/Item/add.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Item/_flow.html.twig',
            ],
        ];
    }
}
