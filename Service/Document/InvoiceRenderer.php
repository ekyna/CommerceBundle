<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Class InvoiceRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property InvoiceInterface $subject
 */
class InvoiceRenderer extends AbstractRenderer
{
    public function getFilename(): string
    {
        return 'invoice_' . $this->subject->getNumber();
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
