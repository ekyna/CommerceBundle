<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderRenderer
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
     * @inheritDoc
     */
    public function getLastModified()
    {
        return $this->supplierOrder->getUpdatedAt();
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return $this->supplierOrder->getNumber();
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
}
