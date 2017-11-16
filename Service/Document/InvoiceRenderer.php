<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

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
    protected function supports($subject)
    {
        return $subject instanceof InvoiceInterface;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'EkynaCommerceBundle:Document:invoice.html.twig';
    }
}
