<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\CoreBundle\Modal;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartHelper
{
    /**
     * @var SaleHelper
     */
    protected $saleHelper;

    /**
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * @var Modal\Renderer
     */
    protected $modalRenderer;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $cartItemClass;

    /**
     * @var bool
     */
    protected $debug;


    /**
     * Constructor.
     *
     * @param SaleHelper               $saleHelper
     * @param CartProviderInterface    $cartProvider
     * @param Modal\Renderer           $modalRenderer
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $cartItemClass
     * @param bool                     $debug
     */
    public function __construct(
        SaleHelper $saleHelper,
        CartProviderInterface $cartProvider,
        Modal\Renderer $modalRenderer,
        EventDispatcherInterface $eventDispatcher,
        $cartItemClass,
        $debug
    ) {
        $this->saleHelper = $saleHelper;
        $this->cartProvider = $cartProvider;
        $this->modalRenderer = $modalRenderer;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartItemClass = $cartItemClass;
        $this->debug = $debug;
    }

    /**
     * Returns the sale helper.
     *
     * @return SaleHelper
     */
    public function getSaleHelper()
    {
        return $this->saleHelper;
    }

    /**
     * Returns the cart provider.
     *
     * @return CartProviderInterface
     */
    public function getCartProvider()
    {
        return $this->cartProvider;
    }

    /**
     * Builds the cart view.
     *
     * @param CartInterface $cart
     * @param array         $options
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    public function buildView(CartInterface $cart, array $options = [])
    {
        if (!isset($options['taxes_view'])) {
            $options['taxes_view'] = false;
        }

        return $this->saleHelper->buildView($cart, $options);
    }

    /**
     * Initializes 'add to cart'.
     *
     * @param SubjectInterface $subject
     * @param Modal\Modal|null $modal
     *
     * @return AddToCartEvent
     */
    public function initializeAddToCart(SubjectInterface $subject, Modal\Modal $modal = null)
    {
        $event = new AddToCartEvent($subject, $modal);

        $this->eventDispatcher->dispatch(AddToCartEvent::INITIALIZE, $event);

        if ($event->isPropagationStopped()) {
            $this->createEventResponse($event);
        }

        return $event;
    }

    /**
     * Creates the 'add subject to cart' form.
     *
     * @param SubjectInterface $subject
     * @param array            $options
     *
     * @return FormInterface
     */
    public function createAddSubjectToCartForm(SubjectInterface $subject, array $options = [])
    {
        /** @var \Ekyna\Component\Commerce\Cart\Model\CartItemInterface $item */
        $item = new $this->cartItemClass; // TODO Use sale factory (create methods to use interface: SaleInterface, etc)

        $this->getSaleHelper()->getSubjectHelper()->assign($item, $subject);

        $options = array_replace([
            'extended'      => true,
            'submit_button' => false,
            'attr'          => [
                'class' => 'form-horizontal',
            ],
        ], $options);

        $action = $this->getSaleHelper()->getSubjectHelper()->generateAddToCartUrl($subject);
        $action .= '?ex=' . ($options['extended'] ? 1 : 0) . '&sb=' . ($options['submit_button'] ? 1 : 0);

        if ($options['submit_button']) {
            $options['attr']['data-add-to-cart'] = $action;
        } elseif(!isset($options['action'])) {
            $options['action'] = $action;
        }

        return $this
            ->saleHelper
            ->getFormFactory()
            ->create(SaleItemConfigureType::class, $item, $options);
    }

    /**
     * Handles the 'add subject to cart' form submission.
     *
     * @param FormInterface $form
     * @param Request       $request
     * @param Modal\Modal   $modal
     *
     * @return AddToCartEvent|null
     */
    public function handleAddSubjectToCartForm(FormInterface $form, Request $request, Modal\Modal $modal = null)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Ekyna\Component\Commerce\Cart\Model\CartItemInterface $item */
            $item = $form->getData();
            /** @var SubjectInterface $subject */
            $subject = $this->getSaleHelper()->getSubjectHelper()->resolve($item);

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

                $this->eventDispatcher->dispatch(AddToCartEvent::SUCCESS, $event);

                if (null !== $response = $this->createEventResponse($event)) {
                    // Custom header to trigger reload of cart widget
                    $response->headers->set('X-Commerce-Success', 1);
                }
            } catch (\Exception $e) {
                if ($this->debug) {
                    throw $e;
                }

                $this->eventDispatcher->dispatch(AddToCartEvent::FAILURE, $event);

                $this->createEventResponse($event);
            }

            return $event;
        }

        return null;
    }

    /**
     * Creates the event response.
     *
     * @param AddToCartEvent $event
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function createEventResponse(AddToCartEvent $event)
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
