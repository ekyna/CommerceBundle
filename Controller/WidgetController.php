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

        $content = $this->templating->render('EkynaCommerceBundle:Widget:customer.html.twig', [
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

        $content = $this->templating->render('EkynaCommerceBundle:Widget:cart.html.twig', [
            'cart' => $cart,
        ]);

        $response = new Response($content);
        $response->setPrivate();

        return $response;
    }
}