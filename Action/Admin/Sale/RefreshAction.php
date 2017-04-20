<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RefreshAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RefreshAction extends AbstractSaleAction
{
    use XhrTrait;

    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$sale = $this->getSale()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->buildXhrSaleViewResponse($sale);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_sale_refresh',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_refresh',
                'path'     => '/refresh',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.refresh',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'refresh',
            ],
        ];
    }
}
