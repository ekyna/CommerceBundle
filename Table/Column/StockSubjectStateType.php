<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Table;
use Ekyna\Component\Table\View\Cell;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StockSubjectStateType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectStateType extends TextType
{
    /**
     * @var \Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
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
            'value' => $this->constantHelper->renderStockSubjectStateBadge($table->getCurrentRowData()),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('label', 'ekyna_commerce.stock_subject.field.state');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_stock_subject_state';
    }
}
