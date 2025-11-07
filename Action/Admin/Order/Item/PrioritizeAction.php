<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemPrioritizeType;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizeCheckerInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function array_replace;

/**
 * Class PrioritizeAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PrioritizeAction extends AbstractAction implements AdminActionInterface
{
    use XhrTrait;
    use ModalTrait;

    public function __construct(
        private readonly OrderPrioritizeCheckerInterface $prioritizeChecker,
        private readonly OrderPrioritizerInterface       $stockPrioritizer,
    ) {
    }

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $item = $this->context->getResource();

        if (!$item instanceof OrderItemInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$this->prioritizeChecker->checkItem($item)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $data = [
            'quantity'  => $item->getTotalQuantity(),
            'same_sale' => false,
        ];

        $form = $this->createForm(SaleItemPrioritizeType::class, $data, [
            'method'       => 'post',
            'action'       => $this->generateResourcePath($item, static::class),
            'attr'         => [
                'class' => 'form-horizontal',
            ],
            'max_quantity' => $item->getTotalQuantity(),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantity = $form->get('quantity')->getData();
            $sameSale = $form->get('same_sale')->getData();

            $changed = $this
                ->stockPrioritizer
                ->prioritizeItem($item, $quantity, $sameSale);

            if ($changed) {
                $this->getManager($item)->flush();
            }

            return $this->buildXhrSaleViewResponse($item->getRootSale());
        }

        $modal = new Modal('sale.header.item.prioritize');
        $modal
            ->setDomain('EkynaCommerce')
            ->setSize(Modal::SIZE_NORMAL)
            ->setForm($form->createView())
            ->addButton(array_replace(Modal::BTN_SUBMIT, [
                'cssClass' => 'btn-warning',
            ]))
            ->addButton(Modal::BTN_CLOSE);

        return $this->renderModal($modal);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_prioritize',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_prioritize',
                'path'     => '/prioritize',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.prioritize',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'level-up',
            ],
        ];
    }
}
