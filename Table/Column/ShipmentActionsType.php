<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment\GatewayAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Shipment\RenderAction;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentGatewayActions;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\ActionsType;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentStateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentActionsType extends AbstractColumnType
{
    private ShipmentHelper $shipmentHelper;
    private ResourceHelper $resourceHelper;

    public function __construct(ShipmentHelper $shipmentHelper, ResourceHelper $resourceHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
        $this->resourceHelper = $resourceHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $shipment = $row->getData(null);
        if (!$shipment instanceof ShipmentInterface) {
            return;
        }

        $actions = $this->shipmentHelper->getGatewayShipmentActions($shipment);
        if (empty($actions)) {
            return;
        }

        $buttons = $view->vars['buttons'] ?? [];

        // TODO Refactor
        /** @see \Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentRenderer::getGatewayButtons */

        foreach ($actions as $action) {
            $buttons[] = [
                'label'    => ShipmentGatewayActions::getLabel($action),
                'icon'     => ShipmentGatewayActions::getIcon($action),
                'theme'    => ShipmentGatewayActions::getTheme($action),
                'confirm'  => ShipmentGatewayActions::getConfirm($action),
                'target'   => ShipmentGatewayActions::getTarget($action),
                'path'     => $this->resourceHelper->generateResourcePath($shipment, GatewayAction::class, [
                    'action' => $action,
                ]),
                'disabled' => !$this->resourceHelper->isGranted(GatewayAction::class, $shipment),
            ];
        }

        // Bill document
        $buttons[] = [
            'label'    => t('document.type.' . ($shipment->isReturn() ? 'return' : 'shipment')
                . '_bill', [], 'EkynaCommerce'),
            'icon'     => 'file',
            'fa_icon'  => true,
            'theme'    => 'primary',
            'target'   => '_blank',
            'path'     => $this->resourceHelper->generateResourcePath($shipment, RenderAction::class, [
                'type' => DocumentTypes::TYPE_SHIPMENT_BILL,
            ]),
            'disabled' => !$this->resourceHelper->isGranted(RenderAction::class, $shipment),
        ];

        if (!$shipment->getSale()->isReleased()) {
            if (!$shipment->isReturn()) {
                // Form document
                $buttons[] = [
                    'label'    => t('document.type.shipment_form', [], 'EkynaCommerce'),
                    'icon'     => 'check-square-o',
                    'fa_icon'  => true,
                    'theme'    => 'primary',
                    'target'   => '_blank',
                    'path'     => $this->resourceHelper->generateResourcePath($shipment, RenderAction::class, [
                        'type' => DocumentTypes::TYPE_SHIPMENT_FORM,
                    ]),
                    'disabled' => !$this->resourceHelper->isGranted(GatewayAction::class, $shipment),
                ];
            }

            // Edit
            $buttons[] = [
                'label'    => t('button.edit', [], 'EkynaUi'),
                'icon'     => 'pencil',
                'fa_icon'  => true,
                'theme'    => 'warning',
                'path'     => $this->resourceHelper->generateResourcePath($shipment, UpdateAction::class),
                'disabled' => !$this->resourceHelper->isGranted(UpdateAction::class, $shipment),
            ];

            // Remove
            if ($this->shipmentHelper->isShipmentDeleteable($shipment)) {
                $buttons[] = [
                    'label'    => t('button.remove', [], 'EkynaUi'),
                    'icon'     => 'trash',
                    'fa_icon'  => true,
                    'theme'    => 'danger',
                    'path'     => $this->resourceHelper->generateResourcePath($shipment, DeleteAction::class),
                    'disabled' => !$this->resourceHelper->isGranted(DeleteAction::class, $shipment),
                ];
            }
        }

        $view->vars['buttons'] = array_map(function (array $button): array {
            return array_replace(ActionsType::BUTTON_DEFAULTS, $button);
        }, $buttons);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('resource', OrderShipmentInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'actions';
    }

    public function getParent(): ?string
    {
        return ActionsType::class;
    }
}
