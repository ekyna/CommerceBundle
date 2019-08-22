<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Currency\CurrencyRendererInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurrencyType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyType extends AbstractColumnType
{
    /**
     * @var CurrencyRendererInterface
     */
    private $renderer;


    /**
     * Constructor.
     *
     * @param CurrencyRendererInterface $currencyRenderer
     */
    public function __construct(CurrencyRendererInterface $currencyRenderer)
    {
        $this->renderer = $currencyRenderer;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'default'      => false,
            'subject_path' => false,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $subject = $row->getData($options['subject_path']);

        if (!$subject instanceof ExchangeSubjectInterface) {
            return;
        }

        $view->vars['value'] = $this->renderer->renderQuote($view->vars['value'], $subject, $options['default']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'text';
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return TextType::class;
    }
}
