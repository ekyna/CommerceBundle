<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Table\Column\VatDisplayModeType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerGroupType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('business', CType\Column\BooleanType::class, [
                'label'                 => t('customer_group.field.business', [], 'EkynaCommerce'),
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'business',
                'position'              => 20,
            ])
            ->addColumn('registration', CType\Column\BooleanType::class, [
                'label'                 => t('customer_group.field.registration', [], 'EkynaCommerce'),
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 30,
            ])
            ->addColumn('quoteAllowed', CType\Column\BooleanType::class, [
                'label'                 => t('customer_group.field.quote_allowed', [], 'EkynaCommerce'),
                'true_class'            => 'label-warning',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 40,
            ])
            ->addColumn('loyalty', CType\Column\BooleanType::class, [
                'label'       => t('customer.field.loyalty_points', [], 'EkynaCommerce'),
                'true_class'  => 'label-success',
                'false_class' => 'label-default',
                'position'    => 50,
            ])
            ->addColumn('vatDisplayMode', VatDisplayModeType::class, [
                'position' => 60,
            ])
            ->addColumn('default', CType\Column\BooleanType::class, [
                'label'                 => t('field.default', [], 'EkynaUi'),
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 70,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ]);
    }
}
