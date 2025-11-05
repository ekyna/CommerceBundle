<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Action\Admin\AbstractStateAction;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;
use function Symfony\Component\Translation\t;

/**
 * Class ScheduleAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ScheduleAction extends AbstractStateAction
{
    protected function init(): ?Response
    {
        $resource = $this->context->getResource();

        $resource->setStartAt($start = new DateTime());
        $resource->setEndAt((clone $start)->setTime(17, 0));

        return parent::init();
    }

    protected function configureTransition(): array
    {
        return [
            'resource'    => ProductionOrderInterface::class,
            'from_states' => [POState::NEW],
            'to_state'    => POState::SCHEDULED,
            'message'     => t('production_order.message.schedule', [], 'EkynaCommerce'),
            'form_data'   => true,
            'title'       => 'schedule',
        ];
    }

    protected function createConfirmForm(): FormInterface
    {
        $form = parent::createConfirmForm();

        $form
            ->add('startAt', DateTimeType::class, [
                'label' => t('field.start_date', [], 'EkynaUi'),
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => t('field.end_date', [], 'EkynaUi'),
            ]);

        return $form;
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'       => 'production_order_schedule',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_schedule',
                'path'     => '/schedule',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.schedule',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'fa fa-calendar',
            ],
            'options'    => [
                'form_template' => '@EkynaCommerce/Admin/ProductionOrder/_form_schedule.html.twig',
            ],
        ]);
    }
}
