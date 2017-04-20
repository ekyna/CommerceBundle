<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;

use function strtolower;
use function strtoupper;
use function Symfony\Component\Translation\t;

/**
 * Class RenderAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenderAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use FlashTrait;

    private RendererFactory             $rendererFactory;
    private DocumentCalculatorInterface $documentCalculator;

    public function __construct(RendererFactory $rendererFactory, DocumentCalculatorInterface $documentCalculator)
    {
        $this->rendererFactory = $rendererFactory;
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

        // Pre-generate the uid used in InvoicePaymentResolver
        $invoice->getRuntimeUid();
        $invoice = clone $invoice;

        if ($currency = $this->request->query->get('currency')) {
            $currency = strtoupper($currency);
            if ($currency != $invoice->getCurrency()) {
                $invoice->setCurrency($currency);

                $this->documentCalculator->calculate($invoice);
            }
        }

        if ($locale = $this->request->query->get('locale')) {
            $locale = strtolower($locale);
            if ($locale != $invoice->getLocale()) {
                $invoice->setLocale($locale);
            }
        }

        $renderer = $this
            ->rendererFactory
            ->createRenderer($invoice);

        try {
            return $renderer->respond($this->request);
        } catch (PdfException $e) {
            $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

            return $this->redirectToReferer($this->generateResourcePath($invoice->getSale()));
        }
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_invoice_render',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_render',
                'path'     => '/render',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.download',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'primary',
                'icon'         => 'download',
            ],
        ];
    }
}
