<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\NumberType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentWeightType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentWeightType extends AbstractColumnType
{
    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;


    /**
     * Constructor.
     *
     * @param ShipmentHelper $shipmentHelper
     */
    public function __construct(ShipmentHelper $shipmentHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        if (0 < $view->vars['value']) {
            return;
        }

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $row->getData();

        $view->vars = array_replace($view->vars, [
            'value'  => $this->shipmentHelper->getShipmentWeight($shipment),
            'append' => $options['append'] . ' (auto)',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'     => 'ekyna_core.field.weight',
            'precision' => 3,
            'append'    => 'kg',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'number';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return NumberType::class;
    }
}
