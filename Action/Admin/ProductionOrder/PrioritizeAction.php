<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\ProductionPrioritizerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class PrioritizeAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PrioritizeAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    private ProductionPrioritizerInterface $prioritizer;

    public function __construct(ProductionPrioritizerInterface $stockPrioritizer)
    {
        $this->prioritizer = $stockPrioritizer;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $order = $this->context->getResource();
        if (!$order instanceof ProductionOrderInterface) {
            throw new UnexpectedTypeException($order, ProductionOrderInterface::class);
        }

        $redirect = $this->redirectToReferer($this->generateResourcePath($order));

        $changed = $this
            ->prioritizer
            ->prioritize($order);

        if ($changed) {
            $event = $this->getManager()->save($order);

            if (!$event->hasErrors()) {
                $this->addFlash(t('prioritize.production.success', [], 'EkynaCommerce'), 'success');

                return $redirect;
            }

            $this->addFlashFromEvent($event);
        }

        $this->addFlash(t('prioritize.production.failure', [], 'EkynaCommerce'), 'warning');

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_production_order_prioritize',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_prioritize',
                'path'     => '/prioritize',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.prioritize',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'warning',
                'icon'         => 'refresh',
            ],
        ];
    }
}
