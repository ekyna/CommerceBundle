<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Account;

use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function is_null;

/**
 * Class QuoteViewHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuoteViewHelper
{
    public function __construct(
        private readonly ResourceManagerInterface $quoteManager,
        private readonly SaleViewHelper           $saleViewHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly Environment              $twig,
    ) {
    }

    public function buildQuantitiesForm(SaleInterface $sale): FormInterface
    {
        return $this->saleViewHelper->buildQuantitiesForm($sale, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_quote_recalculate', [
                'number' => $sale->getNumber(),
            ]),
        ]);
    }

    public function buildSaleView(QuoteInterface $quote, FormInterface $form = null): SaleView
    {
        $editable = $quote->isEditable() && !$quote->isExpired();

        if (is_null($form) && $editable) {
            $form = $this->buildQuantitiesForm($quote);
        }

        return $this->saleViewHelper->buildSaleView($quote, [
            'taxes_view' => false,
            'private'    => false,
            'editable'   => $editable,
        ], $form);
    }

    public function buildXhrSaleViewResponse(QuoteInterface $quote, FormInterface $form = null): Response
    {
        // We need to refresh the sale to get proper "id/position indexed" collections.
        // TODO move to resource listener : refresh all collections indexed by "id" or "position"
        $this->quoteManager->refresh($quote);

        $content = $this->twig->render('@EkynaCommerce/Account/Sale/response.xml.twig', [
            'sale'      => $quote,
            'sale_view' => $this->buildSaleView($quote, $form),
        ]);

        $response = new Response($content);

        $response->headers->set('Content-type', 'application/xml');

        return $response->setPrivate();
    }
}
