<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Builder\InvoiceSynchronizerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RecalculateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RecalculateAction extends AbstractAction implements AdminActionInterface
{
    use ArchiverTrait;
    use HelperTrait;
    use ManagerTrait;

    private InvoiceSynchronizerInterface $invoiceSynchronizer;
    private InvoiceBuilderInterface      $invoiceBuilder;
    private DocumentCalculatorInterface  $documentCalculator;

    public function __construct(
        InvoiceSynchronizerInterface $invoiceSynchronizer,
        InvoiceBuilderInterface $invoiceBuilder,
        DocumentCalculatorInterface $documentCalculator
    ) {
        $this->invoiceSynchronizer = $invoiceSynchronizer;
        $this->invoiceBuilder = $invoiceBuilder;
        $this->documentCalculator = $documentCalculator;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $invoice = $this->context->getResource();

        if (!$invoice instanceof InvoiceInterface) {
            throw new UnexpectedTypeException($invoice, InvoiceInterface::class);
        }

        $redirect = $this->redirectToReferer(
            $this->generateResourcePath($invoice->getSale())
        );

        if (!$this->archive($invoice)) {
            return $redirect;
        }

        // Synchronizes with shipment
        if ($shipment = $invoice->getShipment()) {
            $this->invoiceSynchronizer->synchronize($shipment, true);
        }

        // Update data
        $this->invoiceBuilder->update($invoice);

        // Recalculate
        $this->documentCalculator->calculate($invoice);

        // Persist
        $event = $this->getManager()->save($invoice);

        $this->addFlashFromEvent($event);

        return $redirect;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_invoice_recalculate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_recalculate',
                'path'     => '/recalculate',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'invoice.button.recalculate',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'default',
                'icon'         => 'calculator',
            ],
        ];
    }
}
