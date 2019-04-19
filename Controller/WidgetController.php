<?php

namespace Ekyna\Bundle\CommerceBundle\Controller;

use Ekyna\Bundle\CommerceBundle\Form\Type\Widget\ContextType;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

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
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $homeRoute;

    /**
     * @var array
     */
    private $locales;


    /**
     * Constructor.
     *
     * @param WidgetHelper          $helper
     * @param EngineInterface       $templating
     * @param FormFactoryInterface  $formFactory
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $homeRoute
     * @param array                 $locales
     */
    public function __construct(
        WidgetHelper $helper,
        EngineInterface $templating,
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $urlGenerator,
        string $homeRoute,
        array $locales
    ) {
        $this->helper = $helper;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->homeRoute = $homeRoute;
        $this->locales = $locales;
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
        $this->assertXhr($request);

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
        $this->assertXhr($request);

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
        $this->assertXhr($request);

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
        $this->assertXhr($request);

        $cart = $this->helper->getCart();

        $content = $this->templating->render('@EkynaCommerce/Widget/cart.html.twig', [
            'cart' => $cart,
        ]);

        $response = new Response($content);
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
    public function contextWidgetAction(Request $request)
    {
        $this->assertXhr($request);

        return new JsonResponse($this->helper->getContextWidgetData());
    }

    /**
     * Context dropdown action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function contextDropdownAction(Request $request)
    {
        $this->assertXhr($request);

        $form = $this->createContextForm($request)->createView();

        $content = $this->templating->render('@EkynaCommerce/Widget/context.html.twig', [
            'form' => $form,
        ]);

        $response = new Response($content);
        $response->setPrivate();

        return $response;
    }

    /**
     * Context change action.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function contextChangeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $form = $this->createContextForm($request);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $new = $form->getData();

            $this
                ->helper
                ->getContextProvider()
                ->changeCurrencyAndCountry($new['currency'], $new['country']);

            if (!empty($new['route'])) {
                $parameters = $new['param'] ?? [];
                $parameters['_locale'] = $new['locale'];

                return new RedirectResponse($this->urlGenerator->generate($new['route'], $parameters));
            }
        }

        return new RedirectResponse($this->urlGenerator->generate($this->homeRoute, [
            '_locale' => $this->helper->getLocale(),
        ]));
    }

    /**
     * Currency change action.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function currencyChangeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Change current currency
        if ($code = $request->request->get('currency')) {
            $this->helper->getContextProvider()->changeCurrencyAndCountry($code);
        }

        if ($referer = $request->headers->get('referer')) {
            if ($parts = parse_url($referer)) {
                if ($request->getHttpHost() === $parts["host"]) {
                    return new RedirectResponse($referer);
                }
            }
        }

        return new RedirectResponse("/");
    }

    private function getContextFormData(Request $request): array
    {
        return [
            'currency' => $this->helper->getCurrency(),
            'country'  => $this->helper->getCountry(),
            'locale'   => $this->helper->getLocale(),
            'route'    => $request->query->get('route'),
            'param'    => $request->query->get('param'),
        ];
    }

    private function createContextForm(Request $request): FormInterface
    {
        return $this
            ->formFactory
            ->create(ContextType::class, $this->getContextFormData($request), [
                'method'  => 'POST',
                'action'  => $this->helper->getUrlGenerator()->generate('ekyna_commerce_widget_context_change'),
                'locales' => $this->locales,
            ]);
    }

    private function assertXhr(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }
    }
}
