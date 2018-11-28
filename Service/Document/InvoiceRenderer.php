<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Class InvoiceRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceRenderer extends AbstractRenderer
{
    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        if (empty($this->subjects)) {
            throw new LogicException("Please add invoice(s) first.");
        }

        if (1 < count($this->subjects)) {
            return 'invoices';
        }

        /** @var InvoiceInterface $subject */
        $subject = reset($this->subjects);

        return 'invoice_' . $subject->getNumber();
    }

    /**
     * @inheritdoc
     */
    protected function supports($subject)
    {
        return $subject instanceof InvoiceInterface;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return '@EkynaCommerce/Document/invoice.html.twig';
    }
}
