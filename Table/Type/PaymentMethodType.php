<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\MoveDownAction;
use Ekyna\Bundle\AdminBundle\Action\MoveUpAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Column\EntityType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentMethodType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addDefaultSort('position')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 10,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'position' => 20,
            ])
            ->addColumn('available', CType\Column\BooleanType::class, [
                'label'    => t('field.front_office', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addColumn('private', CType\Column\BooleanType::class, [
                'label'       => t('payment_method.field.private', [], 'EkynaCommerce'),
                'true_class'  => 'label-success',
                'false_class' => 'label-warning',
                'position'    => 40,
            ])
            ->addColumn('defaultCurrency', CType\Column\BooleanType::class, [
                'label'    => t('payment_method.field.use_default_currency', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addColumn('currencies', EntityType::class, [
                'label'        => t('currency.label.plural', [], 'EkynaCommerce'),
                'entity_label' => 'code',
                'position'     => 60,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    MoveUpAction::class,
                    MoveDownAction::class,
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ]);
    }
}
