<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RefreshController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RefreshController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper $resourceHelper,
        private readonly QuoteViewHelper     $viewHelper,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('');
        }

        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        return $this->viewHelper->buildXhrSaleViewResponse($quote);
    }
}
