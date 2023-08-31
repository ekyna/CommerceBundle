<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
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

use function is_array;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Use 'anchor' block type with Anchor model(s).
 */
class OrderInvoiceType extends AbstractColumnType
{
    public function __construct(
        private readonly ResourceHelper $resourceHelper
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $invoices = $row->getData($column->getConfig()->getPropertyPath());

        if ($invoices instanceof Collection) {
            $invoices = $invoices->toArray();
        } elseif (!is_array($invoices)) {
            $invoices = [$invoices];
        }

        $output = '';

        foreach ($invoices as $invoice) {
            if (!$invoice instanceof OrderInvoiceInterface) {
                continue;
            }

            $href = $this->resourceHelper->generateResourcePath($invoice->getOrder(), ReadAction::class);
            $summary = $this->resourceHelper->generateResourcePath($invoice, SummaryAction::class);

            /** @noinspection HtmlUnknownTarget */
            /** @noinspection HtmlUnknownAttribute */
            $output .= sprintf(
                '<a href="%s" %s="%s">%s</a>',
                $href,
                Ui::SIDE_DETAIL_ATTR,
                $summary,
                $invoice->getNumber()
            );
        }

        $view->vars['value'] = $output;
    }

    public function applySort(
        AdapterInterface $adapter,
        ColumnInterface $column,
        ActiveSort $activeSort,
        array $options
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return t('order_invoice.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'invoices' : 'invoice';
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
