<?php

namespace Ekyna\Bundle\CommerceBundle\Controller;

use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class WidgetController
 * @package Ekyna\Bundle\CommerceBundle\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetController
{
    /**
     * @var WidgetHelper
     */
    private $helper;

    /**
     * @var WidgetRenderer
     */
    private $renderer;

    /**
     * @var string
     */
    private $homeRoute;


    /**
     * Constructor.
     *
     * @param WidgetHelper   $helper
     * @param WidgetRenderer $renderer
     * @param string         $homeRoute
     */
    public function __construct(WidgetHelper $helper, WidgetRenderer $renderer, string $homeRoute)
    {
        $this->helper    = $helper;
        $this->renderer  = $renderer;
        $this->homeRoute = $homeRoute;
    }

    /**
     * Customer widget action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function customerWidget(Request $request): Response
    {
        $this->assertRequest($request, true);

        return new JsonResponse($this->helper->getCustomerWidgetData());
    }

    /**
     * Customer dropdown action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function customerDropdown(Request $request): Response
    {
        $this->assertRequest($request, true);

        $response = new Response($this->renderer->renderCustomerDropdown());
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
    public function cartWidget(Request $request): Response
    {
        $this->assertRequest($request, true);

        return new JsonResponse($this->helper->getCartWidgetData());
    }

    /**
     * Cart dropdown action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cartDropdown(Request $request): Response
    {
        $this->assertRequest($request, true);

        $response = new Response($this->renderer->renderCartDropDown());
        $response->setPrivate();

        return $response;
    }

    /**
     * Context widget action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contextWidget(Request $request): Response
    {
        $this->assertRequest($request, true);

        return new JsonResponse($this->helper->getContextWidgetData());
    }

    /**
     * Context dropdown action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function contextDropdown(Request $request): Response
    {
        $this->assertRequest($request, true);

        $response = new Response($this->renderer->renderContextDropDown());
        $response->setPrivate();

        return $response;
    }

    /**
     * Context change action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function contextChange(Request $request): Response
    {
        $this->assertRequest($request, false);

        if ($response = $this->helper->handleContextChange($request)) {
            return $response;
        }

        $url = $this->helper->getUrlGenerator()->generate($this->homeRoute, [
            '_locale' => $this->helper->getLocale(),
        ]);

        return new RedirectResponse($url, Response::HTTP_FOUND);
    }

    /**
     * Currency change action.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function currencyChange(Request $request): Response
    {
        $this->assertRequest($request, false);

        $this->helper->handleCurrencyChange($request);

        if ($referer = $request->headers->get('referer')) {
            if ($parts = parse_url($referer)) {
                if ($request->getHttpHost() === $parts["host"]) {
                    return new RedirectResponse($referer);
                }
            }
        }

        return new RedirectResponse("/", Response::HTTP_FOUND);
    }

    /**
     * Request assertion.
     *
     * @param Request $request
     * @param bool    $xhr
     */
    private function assertRequest(Request $request, bool $xhr = true): void
    {
        if ($xhr xor $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
    }
}
