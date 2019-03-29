<?php

namespace Ekyna\Bundle\CommerceBundle\Controller;

use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class WidgetController
 * @package Ekyna\Bundle\CommerceBundle\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetController extends Controller
{
    /**
     * @var WidgetHelper
     */
    private $helper;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param WidgetHelper    $helper
     * @param EngineInterface $templating
     */
    public function __construct(WidgetHelper $helper, EngineInterface $templating)
    {
        $this->helper = $helper;
        $this->templating = $templating;
    }

    /**
     * Customer widget action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function customerWidgetAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse($this->helper->getCustomerWidgetData());
    }

    /**
     * Customer dropdown action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function customerDropdownAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $content = $this->templating->render('@EkynaCommerce/Widget/customer.html.twig', [
            'user' => $this->helper->getUser(),
        ]);

        $response = new Response($content);
        $response->setPrivate();

        return $response;
    }

    /**
     * Cart widget action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function cartWidgetAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse($this->helper->getCartWidgetData());
    }

    /**
     * Cart dropdown action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cartDropdownAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $cart = $this->helper->getCart();

        $content = $this->templating->render('@EkynaCommerce/Widget/cart.html.twig', [
            'cart' => $cart,
        ]);

        $response = new Response($content);
        $response->setPrivate();

        return $response;
    }

    /**
     * Currency change action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function currencyChangeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        // Change current currency
        if ($code = $request->request->get('currency')) {
            $currencyProvider = $this->helper->getCurrencyProvider();
            $currencyProvider->setCurrentCurrency($code);

            // TODO Dispatch and use event

            // Update cart currency
            $cartProvider = $this->helper->getCartProvider();
            if ($cartProvider->hasCart()) {
                $cart = $cartProvider->getCart();

                if (!$cart->isLocked()) {
                    $currency = $currencyProvider->getCurrency();

                    if ($cart->getCurrency() !== $currency) {
                        $cart->setCurrency($currency);
                        $cartProvider->saveCart();
                    }
                }
            }
        }

        if ($referer = $request->headers->get('referer')) {
            if ($parts = parse_url($referer)) {
                if ($request->getHttpHost() === $parts["host"]) {
                    return $this->redirect($referer);
                }
            }
        }

        return $this->redirect("/");
    }
}
