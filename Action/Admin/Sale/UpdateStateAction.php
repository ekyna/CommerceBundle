<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Resolver\SaleStateResolverFactory;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpdateStateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateStateAction extends AbstractSaleAction
{
    use HelperTrait;
    use ManagerTrait;
    use FlashTrait;
    use XhrTrait;

    private SaleUpdaterInterface     $saleUpdater;
    private SaleStateResolverFactory $resolverFactory;
    private bool                     $debug;


    public function __construct(
        SaleUpdaterInterface     $saleUpdater,
        SaleStateResolverFactory $resolverFactory,
        bool                     $debug
    ) {
        $this->saleUpdater = $saleUpdater;
        $this->resolverFactory = $resolverFactory;
        $this->debug = $debug;
    }

    public function __invoke(): Response
    {
        if (!$this->debug) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $changed = $this->saleUpdater->recalculate($sale);

        $resolver = $this->resolverFactory->getResolver($this->context->getConfig()->getEntityInterface());

        $changed = $resolver->resolve($sale) || $changed;

        if ($changed) {
            $event = $this->getManager()->update($sale);

            $this->addFlashFromEvent($event);
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_state_update',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_state_update',
                'path'     => '/state-update',
                'resource' => true,
                'methods'  => ['GET'],
            ],
        ];
    }
}
