<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Action;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Table\Action\AbstractActionType;
use Ekyna\Component\Table\Action\ActionInterface;
use Ekyna\Component\Table\Source\RowInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ShipmentDocumentActionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentDocumentActionType extends AbstractActionType
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
    public function execute(ActionInterface $action, array $options)
    {
        $table = $action->getTable();

        // The selected row's
        $rows = $table->getSourceAdapter()->getSelection(
            $table->getContext()
        );

        //$ids = $table->getContext()->getSelectedIdentifiers();

        $shipments = array_map(function (RowInterface $row) {
            return $row->getData();
        }, $rows);

        if (empty($shipments)) {
            return null;
        }

        if (1 === count($shipments)) {
            /** @var \Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface $shipment */
            $shipment = reset($shipments);

            return new RedirectResponse($this->urlGenerator->generate('ekyna_commerce_order_shipment_admin_render', [
                'orderId'         => $shipment->getOrder()->getId(),
                'orderShipmentId' => $shipment->getId(),
                'type'            => $options['type'],
            ]));
        }

        return new RedirectResponse($this->urlGenerator->generate('ekyna_commerce_admin_order_list_shipment_document', [
            'id'   => array_map(function (ShipmentInterface $shipment) {
                return $shipment->getId();
            }, $shipments),
            'type' => $options['type'],
        ]));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('type')
            ->setAllowedValues('type', ['form', 'bill']);
    }
}
