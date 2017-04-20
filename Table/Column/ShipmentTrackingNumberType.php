<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class ShipmentTrackingNumberType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentTrackingNumberType extends AbstractColumnType
{
    private ShipmentHelper $shipmentHelper;


    public function __construct(ShipmentHelper $shipmentHelper)
    {
        $this->shipmentHelper = $shipmentHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $row->getData(null);

        if ($shipment->hasParcels()) {
            if (false === $parcel = $shipment->getParcels()->first()) {
                return;
            }

            if (!empty($trackingUrl = $this->shipmentHelper->getTrackingUrl($parcel))) {
                /** @noinspection HtmlUnknownTarget */
                $output = sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    $trackingUrl,
                    $parcel->getTrackingNumber()
                );
            } else {
                $output = $parcel->getTrackingNumber();
            }

            $view->vars['value'] = $output . '&nbsp;(&hellip;)';

            return;
        }

        if (!empty($trackingUrl = $this->shipmentHelper->getTrackingUrl($shipment))) {
            /** @noinspection HtmlUnknownTarget */
            $view->vars['value'] = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $trackingUrl,
                $shipment->getTrackingNumber()
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('shipment.field.tracking_number', [], 'EkynaCommerce'),
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
