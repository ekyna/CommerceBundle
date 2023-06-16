<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function strtolower;
use function strtoupper;
use function Symfony\Component\Translation\t;

/**
 * Class RenderAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenderAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use HelperTrait;
    use FlashTrait;

    public function __construct(
        private readonly RendererFactory            $rendererFactory,
        private readonly InvoiceCalculatorInterface $invoiceCalculator
    ) {
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

        // Pre-generate the uid used by InvoicePaymentResolver
        $invoice->getRuntimeUid();
        $invoice = clone $invoice;

        if ($currency = $this->request->query->get('currency')) {
            $currency = strtoupper($currency);
            if ($currency != $invoice->getCurrency()) {
                $invoice->setCurrency($currency);

                $this->invoiceCalculator->calculate($invoice);
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
        } catch (PdfException) {
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
                'path'     => '/render.{_format}',
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

    public static function buildRoute(Route $route, array $options): void
    {
        $route
            ->addDefaults([
                '_format' => 'pdf',
            ])
            ->addRequirements([
                '_format' => 'html|pdf|jpg',
            ]);
    }
}
