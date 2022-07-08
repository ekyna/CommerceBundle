<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

use function count;
use function reset;

/**
 * Class InvoiceRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceRenderer extends AbstractRenderer
{
    public function getFilename(): string
    {
        if (empty($this->subjects)) {
            throw new LogicException('Call addSubject() first.');
        }

        if (1 < count($this->subjects)) {
            return 'invoices';
        }

        /** @var InvoiceInterface $subject */
        $subject = reset($this->subjects);

        return 'invoice_' . $subject->getNumber();
    }

    protected function supports(object $subject): bool
    {
        return $subject instanceof InvoiceInterface;
    }

    protected function getTemplate(): string
    {
        return '@EkynaCommerce/Document/invoice.html.twig';
    }
}
