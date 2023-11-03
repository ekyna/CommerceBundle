<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Dev;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class DocumentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Dev
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DocumentController
{
    public function __construct(
        private readonly RepositoryFactoryInterface $factory,
        private readonly Environment                $twig,
    ) {
    }

    public function invoice(): Response
    {
        /** @var OrderInvoiceInterface $invoice */
        $invoice = $this->factory->getRepository(OrderInvoiceInterface::class)->find(21925);

        $content = $this->twig->render('@EkynaCommerce/Dev/invoice.html.twig', [
            'debug'   => true,
            'format'  => 'pdf',
            'subject' => $invoice,
            //'template' => '',
        ]);

        return new Response($content);
    }

    public function shipment(): Response
    {
        /** @var OrderShipmentInterface $shipment */
        $shipment = $this->factory->getRepository(OrderShipmentInterface::class)->find(28519);

        $content = $this->twig->render('@EkynaCommerce/Dev/shipment.html.twig', [
            'debug'          => true,
            'format'         => 'pdf',
            'subject'        => $shipment,
            // TODO !!!
            'remaining_date' => true,
            'type'           => DocumentTypes::TYPE_SHIPMENT_BILL,
        ]);

        return new Response($content);
    }

    public function shipmentForm(): Response
    {
        /** @var OrderShipmentInterface $shipment */
        $shipment = $this->factory->getRepository(OrderShipmentInterface::class)->find(28519);

        $content = $this->twig->render('@EkynaCommerce/Dev/shipment_form.html.twig', [
            'debug'          => true,
            'format'         => 'pdf',
            'subject'        => $shipment,
            // TODO !!!
            //'remaining_date' => true,
            'type'           => DocumentTypes::TYPE_SHIPMENT_FORM,
        ]);

        return new Response($content);
    }

    public function supplierOrder(): Response
    {
        /** @var SupplierOrderInterface $order */
        $order = $this->factory->getRepository(SupplierOrderInterface::class)->find(3943);

        $content = $this->twig->render('@EkynaCommerce/Dev/supplier_order.html.twig', [
            'debug'          => true,
            'format'         => 'pdf',
            'subject'        => $order,
            'type' => DocumentTypes::TYPE_SUPPLIER_ORDER,
        ]);

        return new Response($content);
    }
}
