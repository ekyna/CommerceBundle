<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Bundle\CommerceBundle\Table\Column\PaymentStateType;
use Ekyna\Bundle\CommerceBundle\Table\Column\ShipmentStateType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

/**
 * Class OrderShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentType extends AbstractOrderListType
{
    /**
     * @var string
     */
    private $shipmentMethodClass;


    /**
     * @inheritDoc
     */
    public function __construct($class, $shipmentMethodClass)
    {
        parent::__construct($class);

        $this->shipmentMethodClass = $shipmentMethodClass;
    }

    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        parent::buildTable($builder, $options);

        $filters = null === $options['order'];

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addColumn('return', CType\Column\BooleanType::class, [
                'label'       => 'ekyna_commerce.shipment.field.return',
                'true_class'  => 'label-warning',
                'false_class' => 'label-default',
                'position'    => 20,
            ])
            ->addColumn('method', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.method',
                'property_path' => 'method.name',
                'position'      => 30,
            ])
            ->addColumn('state', ShipmentStateType::class, [
                'label'    => 'ekyna_core.field.status',
                'position' => 40,
            ])
            ->addColumn('trackingNumber', CType\Column\TextType::class, [
                'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                'position' => 50,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 60,
            ]);

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'position' => 10,
                ])
                ->addFilter('return', CType\Filter\BooleanType::class, [
                    'label'    => 'ekyna_commerce.shipment.field.return',
                    'position' => 20,
                ])
                ->addFilter('method', EntityType::class, [
                    'label'    => 'ekyna_core.field.method',
                    'class'    => $this->shipmentMethodClass,
                    'position' => 30,
                ])
                ->addFilter('state', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_core.field.status',
                    'choices'  => PaymentStates::getChoices(),
                    'position' => 40,
                ])
                ->addFilter('trackingNumber', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                    'position' => 50,
                ])
                ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                    'label'    => 'ekyna_core.field.created_at',
                    'position' => 60,
                ]);
        }
    }
}
