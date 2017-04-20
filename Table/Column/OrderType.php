<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
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

use function array_replace;
use function is_array;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends AbstractColumnType
{
    private ResourceHelper $helper;

    public function __construct(ResourceHelper $helper)
    {
        $this->helper = $helper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $orders = $row->getData($column->getConfig()->getPropertyPath());

        if ($orders instanceof OrderInterface) {
            $href = $this->helper->generateResourcePath($orders, ReadAction::class);

            /** @noinspection HtmlUnknownTarget */
            $view->vars['value'] = sprintf('<a href="%s">%s</a>', $href, $orders->getNumber());

            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-side-detail' => $this->helper->generateResourcePath($orders, SummaryAction::class),
            ]);

            return;
        }

        if ($orders instanceof Collection) {
            $orders = $orders->toArray();
        } elseif (!is_array($orders)) {
            $orders = [$orders];
        }

        $output = '';

        foreach ($orders as $order) {
            if (!$order instanceof OrderInterface) {
                continue;
            }

            $href = $this->helper->generateResourcePath($order, ReadAction::class);
            $summary = $this->helper->generateResourcePath($order, SummaryAction::class);

            /** @noinspection HtmlUnknownTarget */
            $output .= sprintf('<a href="%s" data-side-detail="%s">%s</a>', $href, $summary, $order->getNumber());
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
        $resolver
            ->setDefaults([
                'multiple'      => false,
                'label'         => function (Options $options, $value) {
                    if ($value) {
                        return $value;
                    }

                    return t('order.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
                },
                'property_path' => function (Options $options, $value) {
                    if ($value) {
                        return $value;
                    }

                    return $options['multiple'] ? 'orders' : 'order';
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
