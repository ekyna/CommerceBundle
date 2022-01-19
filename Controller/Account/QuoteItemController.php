<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemCreateFlow;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

use function array_replace;

/**
 * Class QuoteItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemController implements ControllerInterface
{
    use CustomerTrait;
    use QuoteTrait;

    private ContextProviderInterface $contextProvider;
    private FactoryHelperInterface   $factoryHelper;
    private SaleItemCreateFlow       $createFlow;
    private FormFactoryInterface     $formFactory;
    private EventDispatcherInterface $eventDispatcher;
    private SaleHelper               $saleHelper;
    private FlashHelper              $flashHelper;
    private ModalRenderer            $modalRenderer;

    public function __construct(
        // Quote trait
        RepositoryFactoryInterface $repositoryFactory,
        ManagerFactoryInterface    $managerFactory,
        SaleViewHelper             $saleViewHelper,
        UrlGeneratorInterface      $urlGenerator,
        Environment                $twig,
        // This
        ContextProviderInterface   $contextProvider,
        FactoryHelperInterface     $factoryHelper,
        SaleItemCreateFlow         $createFlow,
        FormFactoryInterface       $formFactory,
        EventDispatcherInterface   $eventDispatcher,
        SaleHelper                 $saleHelper,
        FlashHelper                $flashHelper,
        ModalRenderer              $modalRenderer
    ) {
        // Quote trait
        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->saleViewHelper = $saleViewHelper;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;

        // This
        $this->contextProvider = $contextProvider;
        $this->factoryHelper = $factoryHelper;
        $this->createFlow = $createFlow;
        $this->formFactory = $formFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->saleHelper = $saleHelper;
        $this->flashHelper = $flashHelper;
        $this->modalRenderer = $modalRenderer;
    }

    public function add(Request $request): Response
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

                $event = $this->managerFactory->getManager(QuoteInterface::class)->update($quote);

                if ($event->hasErrors()) {
                    // TODO all event messages should be bound to XHR response
                    FormUtil::addErrorsFromResourceEvent($form, $event);
                } else {
                    $this->createFlow->reset();

                    return $this->buildXhrSaleViewResponse($quote);
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

        $action = $this->urlGenerator->generate('ekyna_commerce_account_quote_item_configure', [
            'number' => $quote->getNumber(),
            'id'     => $item->getId(),
        ]);

        $form = $this->formFactory->create(SaleItemConfigureType::class, $item, [
            'method' => 'post',
            'action' => $action,
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->managerFactory->getManager(QuoteInterface::class)->update($quote);

            return $this->buildXhrSaleViewResponse($quote);
        }

        $modal = new Modal();
        $modal
            ->setTitle('sale.header.item.configure')
            ->setDomain('EkynaCommerce')
            ->setCondensed(true)
            ->setForm($form->createView())
            ->addButton(array_replace(Modal::BTN_SUBMIT, [
                'label' => 'button.save',
            ]))
            ->addButton(Modal::BTN_CANCEL);

        return $this->modalRenderer->render($modal);
    }

    public function moveUp(Request $request): Response
    {
        $quote = $this->findQuote($request);

        $item = $this->findItem($request, $quote);

        if (0 < $item->getPosition()) {
            $item->setPosition($item->getPosition() - 1);

            $this->managerFactory->getManager(QuoteItemInterface::class)->update($item);
        }

        return $this->buildXhrSaleViewResponse($quote);
    }

    public function moveDown(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if (!$item->isLast()) {
            $item->setPosition($item->getPosition() + 1);

            $this->managerFactory->getManager(QuoteItemInterface::class)->update($item);
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

        $this->managerFactory->getManager(QuoteItemInterface::class)->delete($item);

        return $this->buildXhrSaleViewResponse($quote);
    }

    /**
     * Finds the quote.
     *
     * @param Request $request
     *
     * @return QuoteInterface
     */
    private function findQuote(Request $request): QuoteInterface
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        $customer = $this->getCustomer();

        /** @var QuoteInterface $quote */
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $quote = $this
            ->repositoryFactory->getRepository(QuoteInterface::class)
            ->findOneByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (null === $quote) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Quote is not editable.');
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
    private function findItem(Request $request, QuoteInterface $quote): QuoteItemInterface
    {
        $itemId = $request->attributes->getInt('id');
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        /** @var QuoteItemInterface $item */
        if (null === $item = $this->saleHelper->findItemById($quote, $itemId)) {
            throw new NotFoundHttpException('Item not found.');
        }

        return $item;
    }

    private function buildQuantitiesForm(SaleInterface $sale): FormInterface
    {
        $action = $this->urlGenerator->generate('ekyna_commerce_account_quote_recalculate', [
            'number' => $sale->getNumber(),
        ]);

        return $this->saleViewHelper->buildQuantitiesForm($sale, [
            'action' => $action,
        ]);
    }
}
