<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Preparer\SalePreparerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class AbortAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AbortAction extends AbstractOrderAction
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

        $shipment = $this
            ->salePreparer
            ->abort($order);

        if (null !== $shipment) {
            $em = $this->getManager(OrderShipmentInterface::class);
            $em->remove($order);
            $em->flush();

            $this->addFlash(t('sale.abort.success', [], 'EkynaCommerce'), 'success');
        }

        return $this->redirectToReferer($this->generateResourcePath($order));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_order_abort',
            'permission' => Permission::UPDATE, // TODO PREPARE ? or CREATE on ekyna_commerce.shipment ?
            'route'      => [
                'name'     => 'admin_%s_abort',
                'path'     => '/abort',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'sale.button.abort',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'danger',
                'icon'         => 'list',
            ],
        ];
    }
}
