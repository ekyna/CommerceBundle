<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\ORM\Query\Expr;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\CustomerRenderer;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Context\ActiveSort;
use Ekyna\Component\Table\Exception\LogicException;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

use function sprintf;

/**
 * Class CustomerFlagsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerFlagsType extends AbstractColumnType
{
    public function __construct(
        private readonly CustomerRenderer $customerRenderer,
        private readonly string           $orderClass,
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $customer = $row->getData(null);
        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $country = $customer->getDefaultInvoiceAddress()?->getCountry()->getCode();

        $view->vars['value'] = $this
            ->customerRenderer
            ->renderCustomerFlags((int)$row->getExtra('orderCount'), $country);
    }

    public function configureAdapter(AdapterInterface $adapter, ColumnInterface $column, array $options): void
    {
        if (!$adapter instanceof EntityAdapter) {
            $message = sprintf(
                '%s column can only be used with %s adapter.',
                self::class,
                EntityAdapter::class
            );

            throw new LogicException($message);
        }

        $qb = $adapter->getQueryBuilder();
        $ex = $qb->expr();
        $alias = $qb->getRootAliases()[0];

        $sQb1 = $adapter->getManager()->createQueryBuilder();
        $sQb1
            ->select('o.id')
            ->from($this->orderClass, 'o')
            ->where('o.customer = ' . $alias)
            ->groupBy('o.customer');

        $qb
            ->addSelect('dia', 'diac', "({$sQb1->getDQL()}) as orderCount")
            ->leftJoin($alias . '.addresses', 'dia', Expr\Join::WITH, $ex->eq('dia.invoiceDefault', 1))
            ->leftJoin('dia.country', 'diac');

        parent::configureAdapter($adapter, $column, $options);
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

        $adapter
            ->getQueryBuilder()
            ->addOrderBy('orderCount', $activeSort->getDirection());

        return true;
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
