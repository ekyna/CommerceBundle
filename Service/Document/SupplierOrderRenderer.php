<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRenderer extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        if (empty($this->subjects)) {
            throw new LogicException("Please add supplier order(s) first.");
        }

        if (1 < count($this->subjects)) {
            return 'supplier_orders';
        }

        /** @var SupplierOrderInterface $subject */
        $subject = reset($this->subjects);

        return 'supplier_order_' . $subject->getNumber();
    }

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
        return '@EkynaCommerce/Document/supplier_order.html.twig';
    }
}
