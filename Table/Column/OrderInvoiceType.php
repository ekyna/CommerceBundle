<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function is_array;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends AbstractColumnType
{
    private ResourceHelper $resourceHelper;

    public function __construct(ResourceHelper $resourceHelper)
    {
        $this->resourceHelper = $resourceHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $invoices = $row->getData($column->getConfig()->getPropertyPath());

        if ($invoices instanceof OrderInvoiceInterface) {
            $href = $this->resourceHelper->generateResourcePath($invoices->getOrder(), ReadAction::class);

            /** @noinspection HtmlUnknownTarget */
            $view->vars['value'] = sprintf('<a href="%s">%s</a>', $href, $invoices->getNumber());

            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-side-detail' => $this->resourceHelper->generateResourcePath($invoices, SummaryAction::class),
            ]);

            return;
        }

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
            $summary = $this->resourceHelper->generateResourcePath($invoices, SummaryAction::class);

            /** @noinspection HtmlUnknownTarget */
            $output .= sprintf('<a href="%s" data-side-detail="%s">%s</a>', $href, $summary, $invoice->getNumber());
        }

        $view->vars['value'] = $output;
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
