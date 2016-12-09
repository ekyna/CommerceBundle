<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\View\Cell;

/**
 * Class OrderCustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCustomerType extends TextType
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
     * {@inheritdoc}
     */
    public function buildViewCell(Cell $cell, Table $table, array $options)
    {
        $value = $this->helper->renderIdentity($table->getCurrentRowData());

        if (0 < strlen($company = $table->getCurrentRowData('company'))) {
            $value = sprintf('<strong>%s</strong> %s', $company, $value);
        }

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        if (null !== $customer = $table->getCurrentRowData('customer')) {
            $cell->setVars([
                'type'       => 'anchor',
                'value'      => $value,
                'sorted'     => $options['sorted'],
                'route'      => 'ekyna_commerce_customer_admin_show',
                'parameters' => ['customerId' => $customer->getId()],
            ]);
        } else {
            $cell->setVars([
                'type'   => 'text',
                'value'  => $value,
                'sorted' => $options['sorted'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_sale_customer';
    }
}
