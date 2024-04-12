<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Context\ActiveSort;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function implode;
use function is_array;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class OrderShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Use 'anchor' block type with Anchor model(s).
 */
class OrderShipmentType extends AbstractColumnType
{
    public function __construct(
        private readonly ResourceHelper $resourceHelper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $output = '';

        foreach ($this->getShipments($column, $row) as $shipment) {
            if (!$shipment instanceof OrderShipmentInterface) {
                continue;
            }

            $href = $this->resourceHelper->generateResourcePath($shipment->getOrder(), ReadAction::class);
            $summary = $this->resourceHelper->generateResourcePath($shipment, SummaryAction::class);

            /** @noinspection HtmlUnknownTarget */
            /** @noinspection HtmlUnknownAttribute */
            $output .= sprintf(
                '<a href="%s" %s="%s">%s</a>',
                $href,
                Ui::SIDE_DETAIL_ATTR,
                $summary,
                $shipment->getNumber()
            );
        }

        $view->vars['value'] = $output;
    }

    public function applySort(
        AdapterInterface $adapter,
        ColumnInterface  $column,
        ActiveSort       $activeSort,
        array            $options
    ): bool {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        $property = $column->getConfig()->getPropertyPath();
        $property .= empty($property) ? 'number' : '.number';
        $property = $adapter->getQueryBuilderPath($property);

        $adapter
            ->getQueryBuilder()
            ->addOrderBy($property, $activeSort->getDirection());

        return true;
    }

    public function export(ColumnInterface $column, RowInterface $row, array $options): ?string
    {
        return implode(', ', array_map(
            fn (ShipmentInterface $shipment): string => $shipment->getNumber(),
            $this->getShipments($column, $row)
        ));
    }

    /**
     * Retrieves the shipments for a given column and row.
     *
     * @param ColumnInterface $column The column object.
     * @param RowInterface    $row    The row object.
     *
     * @return array<int, ShipmentInterface> The array of shipments.
     */
    private function getShipments(ColumnInterface $column, RowInterface $row): array
    {
        $shipments = $row->getData($column->getConfig()->getPropertyPath());

        if ($shipments instanceof Collection) {
            return $shipments->toArray();
        }

        return is_array($shipments) ? $shipments : [$shipments];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return t('order_shipment.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'shipments' : 'shipment';
            },
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return ColumnType::class;
    }
}
