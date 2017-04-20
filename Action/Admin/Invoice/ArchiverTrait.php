<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\CommerceBundle\Service\Invoice\InvoiceArchiver;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Exception\PdfException;

use function Symfony\Component\Translation\t;

/**
 * Trait ArchiverTrait
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait ArchiverTrait
{
    use FlashTrait;

    private InvoiceArchiver $invoiceArchiver;

    public function setInvoiceArchiver(InvoiceArchiver $invoiceArchiver): void
    {
        $this->invoiceArchiver = $invoiceArchiver;
    }

    protected function archive(InvoiceInterface $invoice): bool
    {
        try {
            $event = $this->invoiceArchiver->archive($invoice);
        } catch (PdfException $e) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return false;
        }

        if ($event->hasErrors()) {
            $this->addFlashFromEvent($event);

            return false;
        }

        return true;
    }
}
