<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use function array_replace;

/**
 * Class CartHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartHelper
{
    public function __construct(
        private readonly SaleHelper               $saleHelper,
        private readonly SubjectHelperInterface   $subjectHelper,
        private readonly SaleViewHelper           $saleViewHelper,
        private readonly FactoryHelperInterface   $factoryHelper,
        private readonly CartProviderInterface    $cartProvider,
        private readonly ModalRenderer            $modalRenderer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly bool                     $debug
    ) {
    }

    public function getSaleHelper(): SaleHelper
    {
        return $this->saleHelper;
    }

    public function getCartProvider(): CartProviderInterface
    {
        return $this->cartProvider;
    }

    /**
     * Builds the cart view.
     */
    public function buildView(CartInterface $cart): SaleView
    {
        return $this->saleViewHelper->buildSaleView($cart, [
            'private'    => false,
            'taxes_view' => false,
            'editable'   => true,
        ]);
    }

    /**
     * Initializes 'add to cart'.
     */
    public function initializeAddToCart(SubjectInterface $subject, Modal $modal = null): AddToCartEvent
    {
        $event = new AddToCartEvent($subject, $modal, null);

        $this->eventDispatcher->dispatch($event, AddToCartEvent::INITIALIZE);

        if ($event->isPropagationStopped()) {
            $this->createEventResponse($event);
        }

        return $event;
    }

    /**
     * Creates the 'add subject to cart' form.
     */
    public function createAddSubjectToCartForm(SubjectInterface $subject, array $options = []): FormInterface
    {
        /** @var CartItemInterface $item */
        $cartClass = $this->cartProvider->getCartClass();
        $item = $this->factoryHelper->createItemForSale(new $cartClass());

        $this->subjectHelper->assign($item, $subject);

        $options = array_replace([
            'extended'      => true,
            'submit_button' => false,
            'attr'          => [
                'class' => 'form-horizontal',
            ],
        ], $options);

        $action = $this->subjectHelper->generateAddToCartUrl($subject);
        $action .= '?ex=' . ($options['extended'] ? 1 : 0) . '&sb=' . ($options['submit_button'] ? 1 : 0);

        if ($options['submit_button']) {
            $options['attr']['data-add-to-cart'] = $action;
        } elseif (!isset($options['action'])) {
            $options['action'] = $action;
        }

        return $this
            ->saleHelper
            ->getFormFactory()
            ->create(SaleItemConfigureType::class, $item, $options);
    }

    /**
     * Handles the 'add subject to cart' form submission.
     */
    public function handleAddSubjectToCartForm(
        FormInterface $form,
        Request       $request,
        Modal         $modal = null
    ): ?AddToCartEvent {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CartItemInterface $item */
            $item = $form->getData();
            /** @var SubjectInterface $subject */
            $subject = $this->subjectHelper->resolve($item);

            $event = new AddToCartEvent($subject, $modal, $item);

            try {
                $cart = $this->cartProvider->getCart(true);

                // TODO addItem() may return the 'merged in' sale item.
                // We need to provide the real added quantity (not the resulting one),
                // so that the google tracking event subscriber can calculate the proper
                // price value
                /** @noinspection PhpParamsInspection */
                $event->setItem($this->saleHelper->addItem($cart, $item));

                $this->cartProvider->saveCart();

                $this->eventDispatcher->dispatch($event, AddToCartEvent::SUCCESS);

                if (null !== $response = $this->createEventResponse($event)) {
                    // Custom header to trigger reload of cart widget
                    $response->headers->set('X-Commerce-Success', 1);
                }
            } catch (Throwable $exception) {
                if ($this->debug) {
                    throw $exception;
                }

                $this->eventDispatcher->dispatch($event, AddToCartEvent::FAILURE);

                $this->createEventResponse($event);
            }

            return $event;
        }

        return null;
    }

    /**
     * Creates the event response.
     */
    protected function createEventResponse(AddToCartEvent $event): ?Response
    {
        if (null !== $response = $event->getResponse()) {
            return $response;
        }

        if (null !== $modal = $event->getModal()) {
            $response = $this->modalRenderer->render($modal);
            $event->setResponse($response);

            return $response;
        }

        return null;
    }
}
