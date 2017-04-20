<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order;

use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class PrioritizeAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PrioritizeAction extends AbstractOrderAction
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    private StockPrioritizerInterface $stockPrioritizer;

    public function __construct(StockPrioritizerInterface $stockPrioritizer)
    {
        $this->stockPrioritizer = $stockPrioritizer;
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

        $changed = $this
            ->stockPrioritizer
            ->prioritizeSale($order);

        if ($changed) {
            $event = $this->getManager()->save($order);

            if (!$event->hasErrors()) {
                $this->addFlash(t('sale.prioritize.success', [], 'EkynaCommerce'), 'success');

                return $redirect;
            }

            $this->addFlashFromEvent($event);
        }

        $this->addFlash(t('sale.prioritize.failure', [], 'EkynaCommerce'), 'warning');

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_order_prioritize',
            'permission' => Permission::UPDATE, // TODO PREPARE ? or CREATE on ekyna_commerce.shipment ?
            'route'      => [
                'name'     => 'admin_%s_prioritize',
                'path'     => '/prioritize',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'sale.button.prioritize',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'warning',
                'icon'         => 'refresh',
            ],
        ];
    }
}
