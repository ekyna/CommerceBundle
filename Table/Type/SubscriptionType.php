<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\Column\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SubscriptionStatus;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SubscriptionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionType extends AbstractTableType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->setSortable(false)
            ->setBatchable(false)
            ->setConfigurable(false)
            ->setExportable(false)
            ->setFilterable(false)
            ->setProfileable(false)
            ->addColumn('audience', BType\Column\AnchorType::class, [
                'label'         => t('audience.label.plural', [], 'EkynaCommerce'),
                'resource_path' => 'audience',
                'position'      => 20,
            ])
            ->addColumn('status', ConstantChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaUi'),
                'class'    => SubscriptionStatus::class,
                'theme'    => true,
                'position' => 30,
            ]);
    }
}
