<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\NumberType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentWeightType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentWeightType extends AbstractColumnType
{
    private ShipmentHelper $shipmentHelper;


    public function __construct(ShipmentHelper $shipmentHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (0 < $view->vars['value']) {
            return;
        }

        /** @var ShipmentInterface $shipment */
        $shipment = $row->getData(null);

        $view->vars = array_replace($view->vars, [
            'value'  => $this->shipmentHelper->getShipmentWeight($shipment),
            'append' => $options['append'] . ' (auto)',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'    => t('field.weight', [], 'EkynaUi'),
            'precision' => 3,
            'append'    => 'kg',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'number';
    }

    public function getParent(): ?string
    {
        return NumberType::class;
    }
}
