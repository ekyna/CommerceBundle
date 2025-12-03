<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type as AType;
use Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder\ScheduleAction;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class ProductionOrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->setExportable(true)
            ->setConfigurable(true)
            ->setProfileable(true)
            ->addDefaultSort('number', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 10,
            ])
            ->addColumn('bom', AType\Column\ResourceType::class, [
                'resource' => BillOfMaterialsInterface::class,
                'position' => 20,
            ])
            ->addColumn('subject', Type\Column\SubjectReferenceType::class, [
                'position' => 30,
            ])
            ->addColumn('quantity', Type\Column\ProductionOrderQuantityType::class, [
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('state', AType\Column\EnumType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 50,
            ])
            ->addColumn('startAt', CType\Column\DateTimeType::class, [
                'label'    => t('field.start_date', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 60,
            ])
            ->addColumn('endAt', CType\Column\DateTimeType::class, [
                'label'    => t('field.end_date', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 70,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    ScheduleAction::class => [
                        'filter' => function (RowInterface $row): bool {
                            /** @var ProductionOrderInterface $order */
                            $order = $row->getData(null);

                            return !POState::isStockableState($order);
                        }
                    ],
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('bom', ResourceType::class, [
                'resource' => BillOfMaterialsInterface::class,
                'position' => 20,
            ])
            ->addFilter('subject', Type\Filter\SubjectReferenceType::class, [
                'context'  => SubjectProviderInterface::CONTEXT_SUPPLIER,
                'position' => 30,
            ])
            ->addFilter('state', AType\Filter\EnumType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'class'    => POState::class,
                'position' => 50,
            ]);
    }
}
