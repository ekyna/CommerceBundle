<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class WarehouseType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WarehouseType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('countries', DType\Column\EntityType::class, [
                'label'        => t('field.country', [], 'EkynaUi'),
                'entity_label' => 'name',
                'position'     => 10,
            ])
            ->addColumn('office', CType\Column\BooleanType::class, [
                'label'    => t('warehouse.field.office', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'    => t('field.default', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addColumn('priority', CType\Column\NumberType::class, [
                'label'     => t('field.priority', [], 'EkynaUi'),
                'position'  => 60,
                'precision' => 0,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('countries', ResourceType::class, [
                'resource' => 'ekyna_commerce.country',
                'position' => 20,
            ])
            ->addFilter('office', CType\Filter\BooleanType::class, [
                'label'    => t('warehouse.field.office', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addFilter('default', CType\Filter\BooleanType::class, [
                'label'    => t('field.default', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addFilter('priority', CType\Filter\NumberType::class, [
                'label'    => t('field.priority', [], 'EkynaUi'),
                'position' => 60,
            ]);
    }
}
