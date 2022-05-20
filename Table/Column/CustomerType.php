<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\TableBundle\Extension\Type\Column\AnchorType;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function json_encode;
use function Symfony\Component\Translation\t;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @deprecated Use ResourceType column type
 * @TODO Remove
 */
class CustomerType extends AbstractColumnType
{
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (null === $customer = $row->getData('customer')) {
            return;
        }

        $view->vars['attr'] = array_replace($view->vars['attr'], [
            'data-summary' => json_encode([
                'route'      => 'admin_ekyna_commerce_customer_summary', // TODO Get route from resource helper
                'parameters' => ['customerId' => $customer->getId()],
            ]),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'                => t('customer.label.singular', [], 'EkynaCommerce'),
            'property_path'        => 'customer',
            'sortable' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'anchor';
    }

    public function getParent(): ?string
    {
        return AnchorType::class;
    }
}
