<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractController
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;


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
     * Sets the url generator.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Sets the cart helper.
     *
     * @param CartHelper $cartHelper
     */
    public function setCartHelper(CartHelper $cartHelper)
    {
        $this->cartHelper = $cartHelper;
    }

    /**
     * Sets the customer provider.
     *
     * @param CustomerProviderInterface $customerProvider
     */
    public function setCustomerProvider(CustomerProviderInterface $customerProvider)
    {
        $this->customerProvider = $customerProvider;
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
     * @see UrlGeneratorInterface::generate()
     */
    protected function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
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
     * Translates the message.
     *
     * @param string $id
     * @param array  $parameters
     * @param null   $domain
     * @param null   $locale
     *
     * @return string
     *
     * @see TranslatorInterface::trans()
     */
    protected function translate($id, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
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
    protected function render($view, array $parameters = [], Response $response = null)
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
     * Saves the cart.
     */
    protected function saveCart()
    {
        $this->cartHelper->getCartProvider()->saveCart();
    }

    /**
     * Returns the current (logged in) customer.
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerInterface|null
     */
    protected function getCustomer()
    {
        return $this->customerProvider->getCustomer();
    }

    /**
     * Returns the cartHelper.
     *
     * @return CartHelper
     */
    protected function getCartHelper()
    {
        return $this->cartHelper;
    }

    /**
     * Returns the sale helper.
     *
     * @return \Ekyna\Bundle\CommerceBundle\Service\SaleHelper
     * @deprecated
     */
    protected function getSaleHelper()
    {
        return $this->cartHelper->getSaleHelper();
    }

    /**
     * Returns the sale factory.
     *
     * @return \Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface
     * @deprecated
     */
    protected function getSaleFactory()
    {
        return $this->getSaleHelper()->getSaleFactory();
    }

    /**
     * Returns the form factory.
     *
     * @return \Symfony\Component\Form\FormFactoryInterface
     * @deprecated
     */
    protected function getFormFactory()
    {
        return $this->getSaleHelper()->getFormFactory();
    }
}
