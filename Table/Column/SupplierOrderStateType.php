<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\View\Cell;

/**
 * Class SupplierOrderStateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderStateType extends TextType
{
    /**
     * @var ConstantHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantHelper $constantHelper
     */
    public function __construct(ConstantHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildViewCell(Cell $cell, Table $table, array $options)
    {
        parent::buildViewCell($cell, $table, $options);

        $cell->setVars([
            'type'  => 'text',
            'value' => $this->constantHelper->renderSupplierOrderStateBadge($cell->vars['value']),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_supplier_order_state';
    }
}