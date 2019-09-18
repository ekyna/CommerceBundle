<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends AbstractColumnType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $invoices = $row->getData($column->getConfig()->getPropertyPath());

        if ($invoices instanceof OrderInvoiceInterface) {
            $href = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                'orderId' => $invoices->getOrder()->getId(),
            ]);

            $view->vars['value'] = sprintf(
                '<a href="%s">%s</a> ',
                $href,
                $invoices->getNumber()
            );

            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-side-detail' => json_encode([
                    'route'      => 'ekyna_commerce_order_invoice_admin_summary',
                    'parameters' => [
                        'orderId'        => $invoices->getOrder()->getId(),
                        'orderInvoiceId' => $invoices->getId(),
                    ],
                ]),
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

            $href = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                'orderId' => $invoice->getOrder()->getId(),
            ]);

            $summary = json_encode([
                'route'      => 'ekyna_commerce_order_invoice_admin_summary',
                'parameters' => [
                    'orderId'        => $invoice->getOrder()->getId(),
                    'orderInvoiceId' => $invoice->getId(),
                ],
            ]);

            $output .= sprintf(
                '<a href="%s" data-side-detail=\'%s\'>%s</a> ',
                $href,
                $summary,
                $invoice->getNumber()
            );
        }

        $view->vars['value'] = $output;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return 'ekyna_commerce.order_invoice.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'invoices' : 'invoice';
            },
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'text';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ColumnType::class;
    }
}
