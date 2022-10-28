<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Invoice;

use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class DownloadController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DownloadController
{
    public function __construct(
        private readonly OrderResourceHelper   $resourceHelper,
        private readonly RendererFactory       $rendererFactory,
        private readonly FlashHelper           $flashHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $invoice = $this->resourceHelper->findInvoiceByOrderAndId($order, $request->attributes->getInt('id'));

        $renderer = $this
            ->rendererFactory
            ->createRenderer($invoice);

        try {
            return $renderer->respond($request);
        } catch (PdfException) {
            $this->flashHelper->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return new RedirectResponse(
                $this->urlGenerator->generate('ekyna_commerce_account_order_index')
            );
        }
    }
}
