<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\TableBundle\Extension\Type\Column\AnchorType;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        if (null === $customer = $row->getData('customer')) {
            return;
        }

        $view->vars['attr'] = array_replace($view->vars['attr'], [
            'data-summary' => json_encode([
                'route'      => 'ekyna_commerce_customer_admin_summary',
                'parameters' => ['customerId' => $customer->getId()],
            ]),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'                => 'ekyna_commerce.customer.label.singular',
            'property_path'        => 'customer',
            'route_name'           => 'ekyna_commerce_customer_admin_show',
            'route_parameters_map' => [
                'customerId' => 'customer.id',
            ],
            'sortable' => false,
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
