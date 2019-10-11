<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemCreateFlow;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class QuoteItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemController extends AbstractSaleController
{
    /**
     * Add new item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function add(Request $request): Response
    {
        $quote = $this->findQuote($request);

        // Set the context using sale
        $this
            ->get('ekyna_commerce.common.context_provider')
            ->setContext($quote);

        $item = $this
            ->get('ekyna_commerce.sale_factory')
            ->createItemForSale($quote);

        $flow = $this->get(SaleItemCreateFlow::class);
        $flow->setGenericFormOptions([
            'action' => $this->generateUrl('ekyna_commerce_account_quote_item_add', [
                'number' => $quote->getNumber(),
            ]),
            'method' => 'POST',
            'attr'   => ['class' => 'form-horizontal'],
        ]);
        $flow->bind($item);

        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                // TODO validation
                $this->getSaleHelper()->addItem($quote, $item);

                // TODO use ResourceManager
                /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $operator */
                $operator = $this->get('ekyna_commerce.quote.operator');
                $event = $operator->update($quote);

                if ($event->hasErrors()) {
                    // TODO all event messages should be bound to XHR response
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                } else {
                    $flow->reset();

                    return $this->buildXhrSaleViewResponse($quote);
                }
            }
        }

        $modal = $this
            ->createModal('ekyna_commerce.sale.header.item.add', $form->createView())
            ->setCondensed(true)
            ->setButtons([])
            ->setVars([
                'flow'          => $flow,
                'form_template' => '@EkynaCommerce/Admin/Common/Item/_flow.html.twig',
            ]);

        $this->get('event_dispatcher')->dispatch(
            SaleItemModalEvent::EVENT_ADD,
            new SaleItemModalEvent($modal, $item)
        );

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * (Re)Configure the item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function configure(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if (!$item->isConfigurable()) {
            throw new NotFoundHttpException('Item is not configurable.');
        }
        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        $form = $this
            ->createForm(SaleItemConfigureType::class, $item, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_account_quote_item_configure', [
                    'number' => $quote->getNumber(),
                    'id'     => $item->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use operator to update item (cart will be automatically saved)
            $this->get('ekyna_commerce.quote.operator')->update($quote);

            return $this->buildXhrSaleViewResponse($quote);
        }

        $modal = $this
            ->createModal('ekyna_commerce.sale.button.item.configure', $form->createView())
            ->setCondensed(true);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Move up the item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function moveUp(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if (0 < $item->getPosition()) {
            $item->setPosition($item->getPosition() - 1);

            // TODO use ResourceManager
            $this->get('ekyna_commerce.quote_item.operator')->update($item);
        }

        return $this->buildXhrSaleViewResponse($quote);
    }

    /**
     * Move down the item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function moveDown(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if (!$item->isLast()) {
            $item->setPosition($item->getPosition() + 1);

            // TODO use ResourceManager
            $this->get('ekyna_commerce.quote_item.operator')->update($item);
        }

        return $this->buildXhrSaleViewResponse($quote);
    }

    /**
     * Remove the item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function remove(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        $this->get('ekyna_commerce.quote_item.operator')->delete($item);

        return $this->buildXhrSaleViewResponse($quote);
    }

    /**
     * Finds the quote.
     *
     * @param Request $request
     *
     * @return QuoteInterface
     */
    protected function findQuote(Request $request): QuoteInterface
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException("Not yet implemented");
        }

        $customer = $this->getCustomerOrRedirect();

        /** @var QuoteInterface $quote */
        $quote = $this
            ->get('ekyna_commerce.quote.repository')
            ->findOneByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (null === $quote) {
            throw $this->createNotFoundException('Quote not found.');
        }

        if (!$quote->isEditable()) {
            throw $this->createAccessDeniedException('Quote is not editable.');
        }

        return $quote;
    }

    /**
     * Finds the quote item.
     *
     * @param Request        $request
     * @param QuoteInterface $quote
     *
     * @return QuoteItemInterface
     */
    protected function findItem(Request $request, QuoteInterface $quote = null): QuoteItemInterface
    {
        if (!$quote) {
            $quote = $this->findQuote($request);
        }

        $itemId = intval($request->attributes->get('id'));
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        /** @var QuoteItemInterface $item */
        if (null === $item = $this->getSaleHelper()->findItemById($quote, $itemId)) {
            throw new NotFoundHttpException('Item not found.');
        }

        return $item;
    }

    /**
     * Builds the recalculate form.
     *
     * @param SaleInterface $sale
     *
     * @return FormInterface
     */
    protected function buildQuantitiesForm(SaleInterface $sale): FormInterface
    {
        return $this->getSaleHelper()->createQuantitiesForm($sale, [
            'method' => 'post',
            'action' => $this->generateUrl('ekyna_commerce_account_quote_recalculate',
                ['number' => $sale->getNumber()]),
        ]);
    }
}