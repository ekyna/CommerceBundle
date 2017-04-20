<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Context\ActiveSort;
use Ekyna\Component\Table\Extension\Core\Type\Column\DateTimeType;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class SalePaymentCompletedAtType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentCompletedAtType extends AbstractColumnType
{
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        /** @var PaymentInterface|null $payment */
        $payment = null;

        /** @var Collection $payments */
        $payments = $row->getData('payments'); // $column->getConfig()->getPropertyPath()

        /** @var PaymentInterface $p */
        foreach ($payments as $p) {
            if ($p->isRefund()) {
                continue;
            }
            if (PaymentStates::isCompletedState($p)) {
                $payment = $p;
                break;
            }
        }

        $view->vars = array_replace($view->vars, [
            'block_prefix' => 'date_time',
            'value'        => $payment ? $payment->getCompletedAt() : null,
        ]);
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

        $qb = $adapter->getQueryBuilder();
        $ex = $qb->expr();
        $alias = $qb->getRootAliases()[0];

        $qb
            ->leftJoin($alias . '.payments', 'p')
            ->andWhere($ex->eq('p.refund', ':refund'))
            ->andWhere($ex->in('p.state', ':states'))
            ->orderBy('p.completedAt', $activeSort->getDirection())
            ->setParameter('refund', false)
            ->setParameter('states', PaymentStates::getCompletedStates());

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('label', t('sale.field.paid_at', [], 'EkynaCommerce'))
            ->setDefault('property_path', null);
    }

    public function getParent(): ?string
    {
        return DateTimeType::class;
    }
}
