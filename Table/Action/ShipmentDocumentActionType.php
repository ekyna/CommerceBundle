<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function array_map;
use function count;
use function reset;

/**
 * Class ShipmentDocumentActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentDocumentActionType extends AbstractActionType
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action, array $options)
    {
        $table = $action->getTable();

        // The selected row's
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        $shipments = array_map(function (RowInterface $row) {
            return $row->getData(null);
        }, $rows);

        if (empty($shipments)) {
            return null;
        }

        if (1 === count($shipments)) {
            /** @var OrderShipmentInterface $shipment */
            $shipment = reset($shipments);

            return new RedirectResponse($this->urlGenerator->generate('admin_ekyna_commerce_order_shipment_render', [
                'orderId'         => $shipment->getOrder()->getId(),
                'orderShipmentId' => $shipment->getId(),
                'type'            => $options['type'],
            ]));
        }

        return new RedirectResponse($this->urlGenerator->generate('admin_ekyna_commerce_list_order_shipment_document', [
            'id'   => array_map(function (ShipmentInterface $shipment) {
                return $shipment->getId();
            }, $shipments),
            'type' => $options['type'],
        ]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('type')
            ->setAllowedValues('type', DocumentTypes::getShipmentTypes());
    }
}
