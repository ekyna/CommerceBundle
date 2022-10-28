<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item;

use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class AddController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddController extends AbstractItemController
{
    public function __construct(
        protected readonly QuoteResourceHelper    $resourceHelper,
        protected readonly SaleHelper             $saleHelper,
        private readonly ContextProviderInterface $contextProvider,
        private readonly FactoryHelperInterface   $factoryHelper,
        private readonly UrlGeneratorInterface    $urlGenerator,
        private readonly FormFlowInterface        $createFlow,
        private readonly ResourceManagerInterface $quoteManager,
        private readonly QuoteViewHelper          $viewHelper,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ModalRenderer            $modalRenderer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $quote = $this->findQuote($request);

        // Set the context using sale
        $this->contextProvider->setContext($quote);

        $item = $this->factoryHelper->createItemForSale($quote);

        $this->createFlow->setGenericFormOptions([
            'method' => 'post',
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_quote_item_add', [
                'number' => $quote->getNumber(),
            ]),
            'attr'   => ['class' => 'form-horizontal'],
        ]);
        $this->createFlow->bind($item);

        $form = $this->createFlow->createForm();

        if ($this->createFlow->isValid($form)) {
            $this->createFlow->saveCurrentStepData($form);

            if ($this->createFlow->nextStep()) {
                $form = $this->createFlow->createForm();
            } else {
                // TODO validation
                $this->saleHelper->addItem($quote, $item);

                $event = $this->quoteManager->update($quote);

                if ($event->hasErrors()) {
                    // TODO all event messages should be bound to XHR response
                    FormUtil::addErrorsFromResourceEvent($form, $event);
                } else {
                    $this->createFlow->reset();

                    return $this->viewHelper->buildXhrSaleViewResponse($quote);
                }
            }
        }

        $modal = new Modal();
        $modal
            ->setTitle('sale.header.item.add')
            ->setDomain('EkynaCommerce')
            ->setCondensed(true)
            ->setForm($form->createView())
            ->setVars([
                'flow'          => $this->createFlow,
                'form_template' => '@EkynaCommerce/Admin/Common/Item/_flow.html.twig',
            ]);

        $this->eventDispatcher->dispatch(
            new SaleItemModalEvent($modal, $item),
            SaleItemModalEvent::EVENT_ADD
        );

        return $this->modalRenderer->render($modal);
    }
}
