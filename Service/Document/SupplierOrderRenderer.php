<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierOrderRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property SupplierOrderInterface $subject
 */
class SupplierOrderRenderer extends AbstractRenderer
{
    public function getFilename(): string
    {
        return 'supplier_order_' . $this->subject->getNumber();
    }

    protected function getParameters(): array
    {
        return [
            'type' => DocumentTypes::TYPE_SUPPLIER_ORDER,
        ];
    }

    protected function supports(object $subject): bool
    {
        return $subject instanceof SupplierOrderInterface;
    }

    protected function getTemplate(): string
    {
        return '@EkynaCommerce/Document/supplier_order.html.twig';
    }
}
