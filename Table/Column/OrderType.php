<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\TableBundle\Extension\Type\Column\AnchorType;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $view->vars['attr'] = array_replace($view->vars['attr'], [
            'data-summary' => json_encode([
                'route'      => 'ekyna_commerce_order_admin_summary',
                'parameters' => ['orderId' => $row->getData('order')->getId()],
            ]),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'                => 'ekyna_commerce.order.label.singular',
            'property_path'        => 'order.number',
            'route_name'           => 'ekyna_commerce_order_admin_show',
            'route_parameters_map' => [
                'orderId' => 'order.id',
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'anchor';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return AnchorType::class;
    }
}
