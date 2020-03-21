<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class InvoiceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceController extends AbstractController
{
    /**
     * Invoice index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        if ($customer->hasParent()) {
            throw $this->createAccessDeniedException();
        }

        $invoices = $this->findInvoicesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Invoice/index.html.twig', [
            'customer' => $customer,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Invoice show action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        if ($customer->hasParent()) {
            throw $this->createAccessDeniedException();
        }

        $invoice = $this->findInvoiceByCustomerAndNumber($customer, $request->attributes->get('number'));

        $invoices = $this->findInvoicesByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Invoice/show.html.twig', [
            'customer'     => $customer,
            'invoice'      => $invoice,
            'invoices'     => $invoices,
            'route_prefix' => 'ekyna_commerce_account_order',
        ]);
    }

    /**
     * Invoice download action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $invoice = $this->findInvoiceByCustomerAndNumber($customer, $request->attributes->get('number'));

        $renderer = $this
            ->get(RendererFactory::class)
            ->createRenderer($invoice);

        return $renderer->respond($request);
    }

    /**
     * Finds the invoices by customer.
     *
     * @param CustomerInterface $customer
     *
     * @return array|InvoiceInterface[]
     */
    protected function findInvoicesByCustomer(CustomerInterface $customer)
    {
        return $this
            ->get('ekyna_commerce.order_invoice.repository')
            ->findByCustomer($customer);
    }

    /**
     * Finds the invoice by customer and number.
     *
     * @param CustomerInterface $customer
     * @param string            $number
     *
     * @return InvoiceInterface
     */
    protected function findInvoiceByCustomerAndNumber(CustomerInterface $customer, $number)
    {
        $invoice = $this
            ->get('ekyna_commerce.order_invoice.repository')
            ->findOneByCustomerAndNumber($customer, $number);

        if (null === $invoice) {
            throw $this->createNotFoundException('Invoice not found.');
        }

        return $invoice;
    }
}
