<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ArchiveAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ArchiveAction extends AbstractAction implements AdminActionInterface
{
    use ArchiverTrait;
    use HelperTrait;

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->context->getResource();

        if (!$invoice instanceof InvoiceInterface) {
            throw new UnexpectedTypeException($invoice, InvoiceInterface::class);
        }

        $this->archive($invoice);

        return $this->redirectToReferer(
            $this->generateResourcePath($invoice->getSale())
        );
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_invoice_archive',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_archive',
                'path'     => '/archive',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.archive',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'archive',
            ],
        ];
    }
}
