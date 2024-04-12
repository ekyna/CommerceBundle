<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
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
use function array_replace;
use function implode;
use function is_array;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Use 'anchor' block type with Anchor model(s).
 */
class QuoteType extends AbstractColumnType
{
    private ResourceHelper $helper;

    public function __construct(ResourceHelper $helper)
    {
        $this->helper = $helper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $output = '';

        foreach ($this->getQuotes($column, $row) as $quote) {
            if (!$quote instanceof QuoteInterface) {
                continue;
            }

            $href = $this->helper->generateResourcePath($quote, ReadAction::class);
            $summary = $this->helper->generateResourcePath($quote, SummaryAction::class);

            /** @noinspection HtmlUnknownTarget */
            /** @noinspection HtmlUnknownAttribute */
            $output .= sprintf(
                '<a href="%s" %s="%s">%s</a>',
                $href,
                Ui::SIDE_DETAIL_ATTR,
                $summary,
                $quote->getNumber()
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
            fn (QuoteInterface $quote): string => $quote->getNumber(),
            $this->getQuotes($column, $row)
        ));
    }

    /**
     * Retrieves the quotes associated with the given column and row.
     *
     * @param ColumnInterface $column The column for which to retrieve the quotes.
     * @param RowInterface    $row    The row for which to retrieve the quotes.
     *
     * @return array<int, QuoteInterface>
     */
    private function getQuotes(ColumnInterface $column, RowInterface $row): array
    {
        $quotes = $row->getData($column->getConfig()->getPropertyPath());

        if ($quotes instanceof Collection) {
            return $quotes->toArray();
        }

        return is_array($quotes) ? $quotes : [$quotes];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return t('quote.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'quotes' : 'quote';
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
