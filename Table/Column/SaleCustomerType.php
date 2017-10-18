<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Context\ActiveSort;
use Ekyna\Component\Table\Export\Value;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class OrderCustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCustomerType extends AbstractColumnType
{
    /**
     * @var ConstantsHelper
     */
    private $helper;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $helper
     */
    public function __construct(ConstantsHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $value = $this->helper->renderIdentity($row->getData());

        if (0 < strlen($company = $row->getData('company'))) {
            $value = sprintf('<strong>%s</strong> %s', $company, $value);
        }

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        if (null !== $customer = $row->getData('customer')) {
            $view->vars = array_replace($view->vars, [
                'block_prefix' => 'anchor',
                'value'        => $value,
                'route'        => 'ekyna_commerce_customer_admin_show',
                'parameters'   => ['customerId' => $customer->getId()],
            ]);
        } else {
            $view->vars = array_replace($view->vars, [
                'block_prefix' => 'text',
                'value'        => $value,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function exportValue(Value $value, ColumnInterface $column, RowInterface $row, array $options)
    {
        $value->setValue((string) $value->getValue());
    }

    /**
     * @inheritDoc
     */
    public function applySort(AdapterInterface $adapter, ColumnInterface $column, ActiveSort $activeSort, array $options)
    {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        $qb = $adapter->getQueryBuilder();

        $prefix = '';
        if (false !== $path = $column->getConfig()->getPropertyPath()) {
            $prefix = $path . '.';
        }

        foreach (['company', 'lastName', 'firstName'] as $property) {
            $property = $adapter->getQueryBuilderPath($prefix . $property);

            $qb->addOrderBy($property, $activeSort->getDirection());
        }

        return true;
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
        return PropertyType::class;
    }
}
