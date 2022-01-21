<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Filter\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\TicketStates;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\CommerceBundle\Table\Filter;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class TicketType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('internal', CType\Column\BooleanType::class, [
                'label'       => t('field.internal', [], 'EkynaCommerce'),
                'true_class'  => 'label-danger',
                'false_class' => 'label-success',
                'position'    => 20,
            ])
            ->addColumn('state', Column\TicketStateType::class, [
                'label'      => t('field.status', [], 'EkynaCommerce'),
                'admin_mode' => $options['admin_mode'],
                'position'   => 30,
            ])
            ->addColumn('subject', CType\Column\TextType::class, [
                'label'    => t('field.subject', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('orders', Column\OrderType::class, [
                'multiple' => true,
                'position' => 50,
            ])
            ->addColumn('quotes', Column\QuoteType::class, [
                'multiple' => true,
                'position' => 60,
            ])
            ->addColumn('customer', Column\CustomerType::class, [
                'position' => 70,
            ])
            ->addColumn('inCharge', Column\InChargeType::class, [
                'position' => 80,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.date', [], 'EkynaUi'),
                'position'    => 90,
                'time_format' => 'none',
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    DeleteAction::class,
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('internal', CType\Filter\BooleanType::class, [
                'label'    => t('field.internal', [], 'EkynaCommerce'),
                'position' => 20,
            ])
            ->addFilter('state', ConstantChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaCommerce'),
                'class'    => TicketStates::class,
                'position' => 30,
            ])
            ->addFilter('subject', CType\Filter\TextType::class, [
                'label'    => t('field.subject', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addFilter('orders', Filter\OrderType::class, [
                'position' => 50,
            ])
            ->addFilter('quotes', Filter\QuoteType::class, [
                'position' => 60,
            ])
            ->addFilter('customer', Filter\CustomerType::class, [
                'position' => 70,
            ])
            ->addFilter('inCharge', Filter\InChargeType::class, [
                'position' => 50,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'position' => 90,
                'time'     => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'configurable' => true,
            'profileable'  => true,
            'admin_mode'   => true,
        ]);
    }
}
