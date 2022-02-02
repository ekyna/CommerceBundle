<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AbstractFormAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConfigureAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigureAction extends AbstractFormAction
{
    use XhrTrait;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(): Response
    {
        $item = $this->context->getResource();

        if (!$item || !$item instanceof SaleItemInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($item->isImmutable() || !$item->isConfigurable()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $form = $this->getForm();

        if ($response = $this->handleForm($form)) {
            return $response;
        }

        return $this->doRespond($form, Modal::MODAL_UPDATE);
    }

    protected function onPostPersist(): ?Response
    {
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getSale());
        }

        return $this->redirect($this->generateResourcePath($item->getSale()));
    }

    protected function onRenderModal(Modal $modal): ?Response
    {
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        $this->eventDispatcher->dispatch(
            new SaleItemModalEvent($modal, $item),
            SaleItemModalEvent::EVENT_CONFIGURE
        );

        return null;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_item_configure',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_configure',
                'path'     => '/configure',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'sale.button.item.configure',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'cog',
            ],
            'options'    => [
                'type'          => SaleItemConfigureType::class,
                'template'      => '@EkynaCommerce/Admin/Common/Item/configure.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Item/_form_configure.html.twig',
            ],
        ];
    }
}
