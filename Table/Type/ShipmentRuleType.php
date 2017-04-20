<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\CommerceBundle\Table\Column\VatDisplayModeType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentRuleType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'        => t('field.name', [], 'EkynaUi'),
                'position'     => 10,
            ])
            ->addColumn('vatMode', VatDisplayModeType::class, [
                'label'        => t('shipment_rule.field.vat_mode', [], 'EkynaCommerce'),
                'position'     => 20,
            ])
            ->addColumn('methods', DType\Column\EntityType::class, [
                'label'        => t('shipment_method.label.plural', [], 'EkynaCommerce'),
                'entity_label' => 'name',
                'position'     => 30,
            ])
            ->addColumn('countries', DType\Column\EntityType::class, [
                'label'        => t('country.label.plural', [], 'EkynaCommerce'),
                'entity_label' => 'name',
                'position'     => 40,
            ])
            ->addColumn('customerGroups', DType\Column\EntityType::class, [
                'label'        => t('customer_group.label.plural', [], 'EkynaCommerce'),
                'entity_label' => 'name',
                'position'     => 50,
            ])
            ->addColumn('startAt', CType\Column\DateTimeType::class, [
                'label'        => t('shipment_rule.field.start_at', [], 'EkynaCommerce'),
                'time_format'  => 'none',
                'position'     => 60,
            ])
            ->addColumn('endAt', CType\Column\DateTimeType::class, [
                'label'        => t('shipment_rule.field.end_at', [], 'EkynaCommerce'),
                'time_format'  => 'none',
                'position'     => 70,
            ])
            ->addColumn('baseTotal', BType\Column\PriceType::class, [
                'label'        => t('shipment_rule.field.base_total', [], 'EkynaCommerce'),
                'position'     => 80,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'        => t('field.net_price', [], 'EkynaCommerce'),
                'position'     => 90,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ]);
    }
}
