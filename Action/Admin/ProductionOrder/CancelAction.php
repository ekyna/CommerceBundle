<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder;

use Ekyna\Bundle\CommerceBundle\Action\Admin\AbstractStateAction;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Resource\Action\Permission;

use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;
use function Symfony\Component\Translation\t;

/**
 * Class CancelAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CancelAction extends AbstractStateAction
{
    protected function init(): ?Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof ProductionOrderInterface) {
            throw new UnexpectedTypeException($resource, ProductionOrderInterface::class);
        }

        if (!$resource->getProductions()->isEmpty()) {
            $this->addFlash(t('production_order.alert.order_cant_be_canceled', [], 'EkynaCommerce'), 'danger');

            return $this->redirect($this->generateResourcePath($resource));
        }

        return parent::init();
    }

    protected function configureTransition(): array
    {
        return [
            'resource'    => ProductionOrderInterface::class,
            'from_states' => [POState::NEW, POState::SCHEDULED],
            'to_state'    => POState::CANCELED,
            'message'     => t('production_order.message.cancel', [], 'EkynaCommerce'),
            'form_data'   => false,
            'title'       => 'cancel',
        ];
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'       => 'production_order_cancel',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_cancel',
                'path'     => '/cancel',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.cancel',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'remove',
            ],
        ]);
    }
}
