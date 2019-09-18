<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class OrderShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentType extends AbstractColumnType
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
        $shipments = $row->getData($column->getConfig()->getPropertyPath());

        if ($shipments instanceof OrderShipmentInterface) {
            $href = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                'orderId' => $shipments->getOrder()->getId(),
            ]);

            $view->vars['value'] = sprintf(
                '<a href="%s">%s</a> ',
                $href,
                $shipments->getNumber()
            );

            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-side-detail' => json_encode([
                    'route'      => 'ekyna_commerce_order_shipment_admin_summary',
                    'parameters' => [
                        'orderId'        => $shipments->getOrder()->getId(),
                        'orderShipmentId' => $shipments->getId(),
                    ],
                ]),
            ]);

            return;
        }

        if ($shipments instanceof Collection) {
            $shipments = $shipments->toArray();
        } elseif (!is_array($shipments)) {
            $shipments = [$shipments];
        }

        $output = '';

        foreach ($shipments as $shipment) {
            if (!$shipment instanceof OrderShipmentInterface) {
                continue;
            }

            $href = $this->urlGenerator->generate('ekyna_commerce_order_admin_show', [
                'orderId' => $shipment->getOrder()->getId(),
            ]);

            $summary = json_encode([
                'route'      => 'ekyna_commerce_order_shipment_admin_summary',
                'parameters' => [
                    'orderId'        => $shipment->getOrder()->getId(),
                    'orderShipmentId' => $shipment->getId(),
                ],
            ]);

            $output .= sprintf(
                '<a href="%s" data-side-detail=\'%s\'>%s</a> ',
                $href,
                $summary,
                $shipment->getNumber()
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

                return 'ekyna_commerce.order_shipment.label.' . ($options['multiple'] ? 'plural' : 'singular');
            },
            'property_path' => function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                return $options['multiple'] ? 'shipments' : 'shipment';
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
