<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class ReadController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReadController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper $resourceHelper,
        private readonly QuoteViewHelper     $viewHelper,
        private readonly Environment         $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $quoteView = $this->viewHelper->buildSaleView($quote);

        $quotes = $this->resourceHelper->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/read.html.twig', [
            'customer'     => $customer,
            'quote'        => $quote,
            'view'         => $quoteView,
            'quotes'       => $quotes,
            'route_prefix' => 'ekyna_commerce_account_quote',
        ]);

        return (new Response($content))->setPrivate();
    }
}
