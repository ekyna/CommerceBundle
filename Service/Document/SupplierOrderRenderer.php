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
     * @inheritDoc
     */
    protected function supports($subject)
    {
        return $subject instanceof SupplierOrderInterface;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'EkynaCommerceBundle:Document:supplier_order.html.twig';
    }
}
