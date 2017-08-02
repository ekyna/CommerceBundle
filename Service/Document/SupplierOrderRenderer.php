<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class InvoiceRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRenderer extends AbstractRenderer
{
    /**
     * @var SupplierOrderInterface
     */
    private $supplierOrder;


    /**
     * Constructor.
     *
     * @param SupplierOrderInterface $supplierOrder
     */
    public function __construct(SupplierOrderInterface $supplierOrder)
    {
        $this->supplierOrder = $supplierOrder;
    }

    /**
     * @inheritdoc
     */
    protected function getContent()
    {
        return $this->renderView('EkynaCommerceBundle:Document:supplier_order.html.twig', [
            'logo_path'      => $this->logoPath,
            'supplier_order' => $this->supplierOrder,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getLastModified()
    {
        return $this->supplierOrder->getUpdatedAt();
    }

    /**
     * @inheritdoc
     */
    protected function getFilename()
    {
        return $this->supplierOrder->getNumber();
    }
}
