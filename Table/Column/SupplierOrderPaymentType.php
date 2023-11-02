<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Supplier\SupplierRenderer;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderPaymentType extends AbstractColumnType
{
    public function __construct(
        private readonly SupplierRenderer $renderer
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $order = $row->getData(null);

        if (!$order instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($order, SupplierOrderInterface::class);
        }

        $view->vars['value'] = $this->renderer->renderPaymentBadge($order, [
            'forwarder' => $options['forwarder'],
            'date'      => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('forwarder', false)
            ->setAllowedTypes('forwarder', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
