<?php

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

/**
 * Class SalePaymentCompletedAtType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentCompletedAtType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
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

    /**
     * @inheritDoc
     */
    public function applySort(
        AdapterInterface $adapter,
        ColumnInterface $column,
        ActiveSort $activeSort,
        array $options
    ) {
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

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('label', 'ekyna_commerce.sale.field.paid_at')
            ->setDefault('property_path', false);
    }

    /**
     * @inheritDoc
     */
    /*public function getBlockPrefix()
    {
        return 'date_time';
    }*/

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return DateTimeType::class;
    }
}
