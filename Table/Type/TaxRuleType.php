<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class TaxRuleType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addDefaultSort('priority', ColumnSort::DESC)
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'        => t('field.name', [], 'EkynaUi'),
                'position'     => 10,
            ])
            ->addColumn('priority', CType\Column\NumberType::class, [
                'label'        => t('field.priority', [], 'EkynaUi'),
                'position'     => 20,
            ])
            ->addColumn('customer', CType\Column\BooleanType::class, [
                'label'        => t('tax_rule.field.customer', [], 'EkynaCommerce'),
                'position'     => 30,
            ])
            ->addColumn('business', CType\Column\BooleanType::class, [
                'label'        => t('tax_rule.field.business', [], 'EkynaCommerce'),
                'position'     => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'        => t('field.name', [], 'EkynaUi'),
                'position'     => 10,
            ])
            ->addFilter('priority', CType\Filter\NumberType::class, [
                'label'        => t('field.priority', [], 'EkynaUi'),
                'position'     => 20,
            ])
            ->addFilter('customer', CType\Filter\BooleanType::class, [
                'label'        => t('tax_rule.field.customer', [], 'EkynaCommerce'),
                'position'     => 30,
            ])
            ->addFilter('business', CType\Filter\BooleanType::class, [
                'label'        => t('tax_rule.field.business', [], 'EkynaCommerce'),
                'position'     => 40,
            ]);
    }
}
