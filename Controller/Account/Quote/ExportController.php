<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote;

use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
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
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportController
{
    public function __construct(
        private readonly QuoteResourceHelper   $resourceHelper,
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

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

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
            $path = $exporter->export($quote);
        } catch (CommerceExceptionInterface $e) {
            if ($this->debug) {
                throw $e;
            }

            $this->flashHelper->addFlash(t($e->getMessage(), [], 'EkynaCommerce'), 'danger');

            $redirect = $this->urlGenerator->generate('ekyna_commerce_account_quote_read', [
                'number' => $quote->getNumber(),
            ]);

            return new RedirectResponse($redirect);
        }

        return File::buildResponse($path, [
            'file_name' => $quote->getNumber() . '.' . $format,
            'mime_type' => $mimeType,
        ]);
    }
}