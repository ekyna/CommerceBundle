<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Helper\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ExportController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportController implements ControllerInterface
{
    public function __construct(
        private readonly OrderResourceHelper   $resourceHelper,
        private readonly SaleCsvExporter       $csvExporter,
        private readonly SaleXlsExporter       $xlsExporter,
        private readonly FlashHelper           $flashHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly bool                  $debug,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $format = $request->getRequestFormat('csv');
        if ($format === 'csv') {
            $exporter = $this->csvExporter;
            $mimeType = 'text/csv';
        } elseif ($format === 'xls') {
            $exporter = $this->xlsExporter;
            $mimeType = 'application/vnd.ms-excel';
        } else {
            throw new InvalidArgumentException("Unexpected format '$format'");
        }

        try {
            $path = $exporter->export($order);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash(t($e->getMessage(), [], 'EkynaCommerce'), 'danger');

            $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
                'number' => $order->getNumber(),
            ]);

            return new RedirectResponse($redirect);
        }

        return File::buildResponse($path, [
            'file_name' => $order->getNumber() . '.' . $format,
            'mime_type' => $mimeType,
        ]);
    }
}
