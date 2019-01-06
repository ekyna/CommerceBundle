<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderType extends AbstractColumnType
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
        $orders = $row->getData($column->getConfig()->getPropertyPath());

        if ($orders instanceof Collection) {
            $orders = $orders->toArray();
        } elseif (!is_array($orders)) {
            $orders = [$orders];
        }

        $output = '';

        foreach ($orders as $order) {
            if (!$order instanceof OrderInterface) {
                continue;
            }

            $href = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                'orderId' => $order->getId(),
            ]);

            $summary = json_encode([
                'route'      => 'ekyna_commerce_order_admin_summary',
                'parameters' => ['orderId' => $order->getId()],
            ]);

            $output .= sprintf(
                '<a href="%s" data-summary=\'%s\'>%s</a> ',
                $href,
                $summary,
                $order->getNumber()
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

                return 'ekyna_commerce.order.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'orders' : 'order';
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
