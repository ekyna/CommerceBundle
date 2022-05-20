<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemPrioritizeType;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function array_replace;

/**
 * Class PrioritizeAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PrioritizeAction extends AbstractAction implements AdminActionInterface
{
    use XhrTrait;
    use ModalTrait;

    private StockPrioritizerInterface $stockPrioritizer;

    public function __construct(StockPrioritizerInterface $stockPrioritizer)
    {
        $this->stockPrioritizer = $stockPrioritizer;
    }

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$this->stockPrioritizer->canPrioritizeSaleItem($item)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $data = [
            'quantity' => $item->getTotalQuantity(),
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
            $changed = $this
                ->stockPrioritizer
                ->prioritizeSaleItem($item, $form->get('quantity')->getData());

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
                'label'        => 'sale.button.prioritize',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'level-up',
            ],
        ];
    }
}
