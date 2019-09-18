<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends AbstractColumnType
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $quotes = $row->getData($column->getConfig()->getPropertyPath());

        if ($quotes instanceof QuoteInterface) {
            $href = $this->urlGenerator->generate('ekyna_commerce_quote_admin_show', [
                'quoteId' => $quotes->getId(),
            ]);

            $view->vars['value'] = sprintf(
                '<a href="%s">%s</a> ',
                $href,
                $quotes->getNumber()
            );

            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-side-detail' => json_encode([
                    'route'      => 'ekyna_commerce_quote_admin_summary',
                    'parameters' => [
                        'quoteId' => $quotes->getId(),
                    ],
                ]),
            ]);

            return;
        }
        
        if ($quotes instanceof Collection) {
            $quotes = $quotes->toArray();
        } elseif (!is_array($quotes)) {
            $quotes = [$quotes];
        }

        $output = '';

        foreach ($quotes as $quote) {
            if (!$quote instanceof QuoteInterface) {
                continue;
            }

            $href = $this->urlGenerator->generate('ekyna_commerce_quote_admin_show', [
                'quoteId' => $quote->getId(),
            ]);

            $summary = json_encode([
                'route'      => 'ekyna_commerce_quote_admin_summary',
                'parameters' => ['quoteId' => $quote->getId()],
            ]);

            $output .= sprintf(
                '<a href="%s" data-side-detail=\'%s\'>%s</a> ',
                $href,
                $summary,
                $quote->getNumber()
            );
        }

        $view->vars['value'] = $output;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple'      => false,
            'label'         => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return 'ekyna_commerce.quote.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'quotes' : 'quote';
            },
        ]);
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
        return ColumnType::class;
    }
}
