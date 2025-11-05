<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GanttAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GanttAction extends AbstractAction implements AdminActionInterface
{
    use TemplatingTrait;

    public function __invoke(): Response
    {
        return $this->render($this->options['template']);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_production_order_gantt',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_gantt',
                'path'     => '/gantt',
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'production_order.button.gantt',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'fa fa-calendar',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/ProductionOrder/gantt.html.twig',
            ],
        ];
    }
}
