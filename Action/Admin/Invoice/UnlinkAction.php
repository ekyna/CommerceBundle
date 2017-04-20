<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UnlinkAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UnlinkAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
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

        if ($shipment = $invoice->getShipment()) {
            $shipment
                ->setAutoInvoice(false)
                ->setInvoice(null);

            $em = $this->getManager($shipment);
            $em->persist($shipment);
            $em->flush();
        }

        return $this->redirectToReferer(
            $this->generateResourcePath($invoice->getSale())
        );
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_invoice_unlink',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_unlink',
                'path'     => '/unlink',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'invoice.button.unlink',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'unlink',
            ],
        ];
    }
}
