<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

use function count;
use function reset;

/**
 * Class SupplierOrderRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRenderer extends AbstractRenderer
{
    public function getFilename(): string
    {
        if (empty($this->subjects)) {
            throw new LogicException('Call addSubject() first');
        }

        if (1 < count($this->subjects)) {
            return 'supplier_orders';
        }

        /** @var SupplierOrderInterface $subject */
        $subject = reset($this->subjects);

        return 'supplier_order_' . $subject->getNumber();
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
