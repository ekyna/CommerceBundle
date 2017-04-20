<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Preparer\SalePreparerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class PrepareAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PrepareAction extends AbstractOrderAction
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    private SalePreparerInterface $salePreparer;

    public function __construct(SalePreparerInterface $salePreparer)
    {
        $this->salePreparer = $salePreparer;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (!$order = $this->getOrder()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $redirect = $this->redirectToReferer($this->generateResourcePath($order));

        $shipment = $this
            ->salePreparer
            ->prepare($order);

        if (null !== $shipment) {
            $event = $this->getManager()->save($order);

            if (!$event->hasErrors()) {
                $this->addFlash(t('sale.prepare.success', [], 'EkynaCommerce'), 'success');

                return $redirect;
            }

            $this->addFlashFromEvent($event);
        }

        $this->addFlash(t('sale.prepare.failure', [], 'EkynaCommerce'), 'warning');

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_order_prepare',
            'permission' => Permission::UPDATE, // TODO PREPARE ? or CREATE on ekyna_commerce.shipment ?
            'route'      => [
                'name'     => 'admin_%s_prepare',
                'path'     => '/prepare',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'sale.button.prepare',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'primary',
                'icon'         => 'list',
            ],
        ];
    }
}
