<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Manufacture\ManufactureHelper;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ProductionOrderQuantityType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderQuantityType extends AbstractColumnType
{
    public function __construct(
        private readonly ManufactureHelper $productionRenderer
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $order = $row->getData(null);

        $view->vars['value'] = $this->productionRenderer->renderProductionOrderQuantity($order);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('field.quantity', [], 'EkynaUi'));
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
