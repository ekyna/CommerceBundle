<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\DateTimeType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentOutstandingDateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentOutstandingDateType extends AbstractColumnType
{
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        /** @var PaymentInterface $payment */
        $payment = $row->getData(null);

        if ($payment->isRefund()) {
            $view->vars['value'] = null;

            return;
        }

        $view->vars['value'] = $payment->getSale()->getOutstandingDate();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'         => t('sale.field.outstanding_date', [], 'EkynaCommerce'),
            'property_path' => 'order.outstandingDate',
            'time_format'   => 'none',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'date_time';
    }

    public function getParent(): ?string
    {
        return DateTimeType::class;
    }
}
