<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    protected function onInit(): ?Response
    {
        $invoice = $this->context->getResource();

        if (!$invoice instanceof InvoiceInterface) {
            throw new UnexpectedTypeException($invoice, InvoiceInterface::class);
        }

        $invoice->setCredit($this->request->query->getBoolean('credit'));

        return parent::onInit();
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_invoice_create',
            'button'  => [
                'label'        => 'invoice.button.new',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template'      => '@EkynaCommerce/Admin/Common/Invoice/create.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Invoice/_form.html.twig',
            ],
        ]);
    }
}
