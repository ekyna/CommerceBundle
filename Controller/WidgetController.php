<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller;

use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class WidgetController
 * @package Ekyna\Bundle\CommerceBundle\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetController
{
    private WidgetHelper          $helper;
    private WidgetRenderer        $renderer;
    private UrlGeneratorInterface $urlGenerator;
    private string                $homeRoute;

    public function __construct(
        WidgetHelper          $helper,
        WidgetRenderer        $renderer,
        UrlGeneratorInterface $urlGenerator,
        string                $homeRoute
    ) {
        $this->helper = $helper;
        $this->renderer = $renderer;
        $this->urlGenerator = $urlGenerator;
        $this->homeRoute = $homeRoute;
    }

    /**
     * Customer widget action.
     */
    public function customerWidget(Request $request): Response
    {
        $this->assertRequest($request, true);

        return new JsonResponse($this->helper->getCustomerWidgetData());
    }

    public function customerDropdown(Request $request): Response
    {
        $this->assertRequest($request, true);

        $response = new Response($this->renderer->renderCustomerDropdown());
        $response->setPrivate();

        return $response;
    }

    public function cartWidget(Request $request): Response
    {
        $this->assertRequest($request, true);

        return new JsonResponse($this->helper->getCartWidgetData());
    }

    public function cartDropdown(Request $request): Response
    {
        $this->assertRequest($request, true);

        $response = new Response($this->renderer->renderCartDropDown());
        $response->setPrivate();

        return $response;
    }

    public function contextWidget(Request $request): Response
    {
        $this->assertRequest($request, true);

        return new JsonResponse($this->helper->getContextWidgetData());
    }

    public function contextDropdown(Request $request): Response
    {
        $this->assertRequest($request, true);

        $response = new Response($this->renderer->renderContextDropDown());
        $response->setPrivate();

        return $response;
    }

    public function contextChange(Request $request): Response
    {
        $this->assertRequest($request, false);

        if ($response = $this->helper->handleContextChange($request)) {
            return $response;
        }

        $url = $this->urlGenerator->generate($this->homeRoute, [
            '_locale' => $this->helper->getLocale(),
        ]);

        return new RedirectResponse($url, Response::HTTP_FOUND);
    }

    public function currencyChange(Request $request): Response
    {
        $this->assertRequest($request, false);

        $this->helper->handleCurrencyChange($request);

        if ($referer = $request->headers->get('referer')) {
            if ($parts = parse_url($referer)) {
                if ($request->getHttpHost() === $parts['host']) {
                    return new RedirectResponse($referer);
                }
            }
        }

        return new RedirectResponse('/', Response::HTTP_FOUND);
    }

    /**
     * Request assertion.
     */
    private function assertRequest(Request $request, bool $xhr): void
    {
        if ($xhr xor $request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
    }
}
