<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class IndexController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IndexController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper $resourceHelper,
        private readonly Environment         $twig,
    ) {
    }

    public function __invoke(): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quotes = $this->resourceHelper->findQuotesByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Quote/index.html.twig', [
            'customer' => $customer,
            'quotes'   => $quotes,
        ]);

        return (new Response($content))->setPrivate();
    }
}
