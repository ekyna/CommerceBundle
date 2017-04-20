<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class InvoiceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceController implements ControllerInterface
{
    use CustomerTrait;

    private OrderInvoiceRepositoryInterface $invoiceRepository;
    private RendererFactory                 $rendererFactory;
    private FlashHelper                     $flashHelper;
    private UrlGeneratorInterface           $urlGenerator;
    private Environment                     $twig;

    public function __construct(
        OrderInvoiceRepositoryInterface $invoiceRepository,
        RendererFactory                 $rendererFactory,
        FlashHelper                     $flashHelper,
        UrlGeneratorInterface           $urlGenerator,
        Environment                     $twig
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->rendererFactory = $rendererFactory;
        $this->flashHelper = $flashHelper;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function index(): Response
    {
        $customer = $this->getCustomer();

        if ($customer->hasParent()) {
            throw new AccessDeniedHttpException('');
        }

        $invoices = $this->findInvoicesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Invoice/index.html.twig', [
            'customer' => $customer,
            'invoices' => $invoices,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function read(Request $request): Response
    {
        $customer = $this->getCustomer();

        if ($customer->hasParent()) {
            throw new AccessDeniedHttpException('');
        }

        $invoice = $this->findInvoiceByCustomerAndNumber($customer, $request->attributes->get('number'));

        $invoices = $this->findInvoicesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Invoice/show.html.twig', [
            'customer' => $customer,
            'invoice' => $invoice,
            'invoices' => $invoices,
            'route_prefix' => 'ekyna_commerce_account_order',
        ]);

        return (new Response($content))->setPrivate();
    }

    public function download(Request $request): Response
    {
        $customer = $this->getCustomer();

        $invoice = $this->findInvoiceByCustomerAndNumber($customer, $request->attributes->get('number'));

        $renderer = $this
            ->rendererFactory
            ->createRenderer($invoice);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return new RedirectResponse(
                $this->urlGenerator->generate('ekyna_commerce_account_invoice_index')
            );
        }
    }

    /**
     * @return array<OrderInvoiceInterface>
     */
    protected function findInvoicesByCustomer(CustomerInterface $customer): array
    {
        return $this
            ->invoiceRepository
            ->findByCustomer($customer);
    }

    protected function findInvoiceByCustomerAndNumber(
        CustomerInterface $customer,
        string            $number
    ): OrderInvoiceInterface {
        $invoice = $this
            ->invoiceRepository
            ->findOneByCustomerAndNumber($customer, $number);

        if (null === $invoice) {
            throw new NotFoundHttpException('Invoice not found.');
        }

        return $invoice;
    }
}
