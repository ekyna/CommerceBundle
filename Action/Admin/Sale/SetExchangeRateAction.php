<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SetExchangeRateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SetExchangeRateAction extends AbstractSaleAction
{
    use HelperTrait;
    use ManagerTrait;
    use FlashTrait;
    use XhrTrait;

    private SaleUpdaterInterface $saleUpdater;

    public function __construct(SaleUpdaterInterface $saleUpdater)
    {
        $this->saleUpdater = $saleUpdater;
    }

    public function __invoke(): Response
    {
        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($this->saleUpdater->updateExchangeRate($sale)) {
            $event = $this->getManager()->update($sale);

            $this->addFlashFromEvent($event);
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirectToReferer($this->generateResourcePath($sale));
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
