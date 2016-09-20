<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractController
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var CartHelper
     */
    private $cartHelper;


    /**
     * Sets the urlGenerator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Sets the templating.
     *
     * @param EngineInterface $templating
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Sets the cartHelper.
     *
     * @param CartHelper $cartHelper
     */
    public function setCartHelper(CartHelper $cartHelper)
    {
        $this->cartHelper = $cartHelper;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route         The name of the route
     * @param mixed  $parameters    An array of parameters
     * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->urlGenerator->generate($route, $parameters, $referenceType);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Renders the template and returns the response.
     *
     * @param string        $view
     * @param array         $parameters
     * @param Response|null $response
     *
     * @return Response
     */
    protected function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }

    /**
     * Returns the cart.
     *
     * @return CartInterface|null
     */
    protected function getCart()
    {
        return $this->cartHelper->getCartProvider()->getCart();
    }

    /**
     * Returns the cartHelper.
     *
     * @return CartHelper
     */
    public function getCartHelper()
    {
        return $this->cartHelper;
    }

    /**
     * Returns the sale helper.
     *
     * @return \Ekyna\Bundle\CommerceBundle\Helper\SaleHelper
     */
    protected function getSaleHelper()
    {
        return $this->cartHelper->getSaleHelper();
    }
}
