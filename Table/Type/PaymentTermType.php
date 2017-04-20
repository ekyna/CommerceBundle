<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentTermType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'        => t('field.name', [], 'EkynaUi'),
                'position'     => 10,
            ])
            ->addColumn('days', CType\Column\NumberType::class, [
                'label'        => t('payment_term.field.days', [], 'EkynaCommerce'),
                'precision'    => 0,
                'position'     => 20,
            ])
            ->addColumn('endOfMonth', CType\Column\BooleanType::class, [
                'label'        => t('payment_term.field.end_of_month', [], 'EkynaCommerce'),
                'position'     => 30,
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
