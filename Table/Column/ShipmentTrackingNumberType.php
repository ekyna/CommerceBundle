<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentTrackingNumberType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentTrackingNumberType extends AbstractColumnType
{
    const /** @noinspection HtmlUnknownTarget */
        TEMPLATE = '<a href="%s" target="_blank">%s</a>';

    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;


    /**
     * Constructor.
     *
     * @param ShipmentHelper $shipmentHelper
     */
    public function __construct(ShipmentHelper $shipmentHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $row->getData();

        if ($shipment->hasParcels()) {
            if (false === $parcel = $shipment->getParcels()->first()) {
                return;
            }

            if (!empty($trackingUrl = $this->shipmentHelper->getTrackingUrl($parcel))) {
                $output = sprintf(static::TEMPLATE, $trackingUrl, $parcel->getTrackingNumber());
            } else {
                $output = $parcel->getTrackingNumber();
            }

            $view->vars['value'] = $output . '&nbsp;(&hellip;)';

            return;
        }

        if (!empty($trackingUrl = $this->shipmentHelper->getTrackingUrl($shipment))) {
            $view->vars['value'] = sprintf(static::TEMPLATE, $trackingUrl, $shipment->getTrackingNumber());
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_commerce.shipment.field.tracking_number',
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
        return TextType::class;
    }
}
