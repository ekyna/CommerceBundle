<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RecalculateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RecalculateController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper      $resourceHelper,
        private readonly QuoteViewHelper          $viewHelper,
        private readonly SaleUpdaterInterface     $saleUpdater,
        private readonly ResourceManagerInterface $quoteManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('');
        }

        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $form = $this->viewHelper->buildQuantitiesForm($quote);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO recalculate may return false if nothing changed even if quantities are different (sample case)
            if ($this->saleUpdater->recalculate($quote)) {
                $event = $this->quoteManager->save($quote);

                // TODO Some important information to display may have changed (state, etc)

                if ($request->isXmlHttpRequest()) {
                    if ($event->hasErrors()) {
                        foreach ($event->getErrors() as $error) {
                            $form->addError(new FormError($error->getMessage()));
                        }
                    }
                }
            }
        }

        return $this->viewHelper->buildXhrSaleViewResponse($quote, $form);
    }
}
