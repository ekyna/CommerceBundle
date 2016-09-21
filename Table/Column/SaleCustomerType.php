<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\StateHelper;
use Ekyna\Bundle\UserBundle\Helper\IdentityHelper;
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
     * @var IdentityHelper
     */
    private $helper;


    /**
     * Constructor.
     *
     * @param IdentityHelper $helper
     */
    public function __construct(IdentityHelper $helper)
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

        $cell->setVars([
            'type'   => 'text',
            'value'  => $value,
            'sorted' => $options['sorted'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_sale_customer';
    }
}
