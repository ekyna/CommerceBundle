<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Split into actions...
 */
class AbstractController
{
    protected Features $features;
    protected Environment $twig;
    protected UrlGeneratorInterface $urlGenerator;
    protected TranslatorInterface $translator;
    protected CartHelper $cartHelper;
    protected UserProviderInterface $userProvider;
    protected CustomerProviderInterface $customerProvider;
    protected SaleStepValidatorInterface $stepValidator;

    public function setFeatures(Features $features): void
    {
        $this->features = $features;
    }

    public function setEnvironment(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function setCartHelper(CartHelper $cartHelper): void
    {
        $this->cartHelper = $cartHelper;
    }

    public function setUserProvider(UserProviderInterface $userProvider): void
    {
        $this->userProvider = $userProvider;
    }

    public function setCustomerProvider(CustomerProviderInterface $customerProvider): void
    {
        $this->customerProvider = $customerProvider;
    }

    public function setStepValidator(SaleStepValidatorInterface $stepValidator): void
    {
        $this->stepValidator = $stepValidator;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface::generate()
     */
    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->urlGenerator->generate($route, $parameters, $referenceType);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Translates the message.
     *
     * @see TranslatorInterface::trans()
     */
    protected function translate(string $id, array $parameters = [], string $domain = null, $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Renders the template and returns the response.
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $content = $this->twig->render($view, $parameters);

        if (!$response) {
            $response = new Response();
        }

        return $response->setContent($content);
    }

    /**
     * Builds the cart controls.
     */
    protected function buildCartControls(SaleInterface $cart = null): array
    {
        $customer = $this->getCustomer();

        $controls = [
            'empty'       => 1,
            'valid'       => 0,
            'errors'      => [],
            'user'        => null !== $this->getUser() ? 1 : 0,
            'customer'    => null !== $customer ? 1 : 0,
            'quote'       => null !== $customer && $customer->getCustomerGroup()->isQuoteAllowed() ? 1 : 0,
            'information' => 1,
            'invoice'     => 0,
            'delivery'    => 0,
            'comment'     => 0,
            'attachments' => 0,
        ];

        if ($cart && 0 < $cart->getItems()->count()) {
            $controls['empty'] = 0;

            if ($cart->isLocked()) {
                $controls['information'] = 0;
            } else {
                if (!$cart->isIdentityEmpty() && !empty($cart->getEmail())) {
                    $controls['invoice'] = 1;
                    if (null !== $cart->getInvoiceAddress()) {
                        $controls['delivery'] = 1;
                        $controls['comment'] = 1;
                        $controls['attachments'] = 1;
                    }
                }

                $valid = $this->stepValidator->validate($cart, SaleStepValidatorInterface::CHECKOUT_STEP);
                if ($valid) {
                    $controls['valid'] = 1;
                } else {
                    /** @var ConstraintViolationInterface $violation */
                    foreach ($this->stepValidator->getViolationList() as $violation) {
                        $controls['errors'][] = $violation->getMessage();
                    }
                }
            }
        }

        return $controls;
    }

    /**
     * Transforms the constraint violation list to session flashes.
     */
    protected function violationToFlashes(ConstraintViolationListInterface $list, Request $request): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $flashes = $session->getFlashBag();

        $messages = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($list as $violation) {
            $messages[] = $violation->getMessage();
        }

        if (!empty($messages)) {
            $flashes->add('danger', implode('<br>', $messages));
        }
    }

    /**
     * Creates the cart items quantities form.
     *
     * @param CartInterface $cart
     *
     * @return FormInterface
     */
    protected function createQuantitiesForm(CartInterface $cart): FormInterface
    {
        return $this->getSaleHelper()->createQuantitiesForm($cart, [
            'method'            => 'post',
            'action'            => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
            'validation_groups' => ['Calculation', 'Availability'],
        ]);
    }

    /**
     * Creates the cart coupon code form.
     *
     * @param CartInterface $cart
     *
     * @return FormInterface
     *
     * @deprecated Use CouponHelper
     */
    protected function createCouponForm(CartInterface $cart): FormInterface
    {
        if ($coupon = $cart->getCoupon()) {
            $action = $this->generateUrl('ekyna_commerce_cart_coupon_clear');
            $code = $coupon->getCode();
        } else {
            $action = $this->generateUrl('ekyna_commerce_cart_coupon_set');
            $code = null;
        }

        return $this->getSaleHelper()->createCouponForm([
            'method' => 'post',
            'action' => $action,
            'code'   => $code,
        ]);
    }

    /**
     * Returns the cart.
     */
    protected function getCart(): ?CartInterface
    {
        return $this->cartHelper->getCartProvider()->getCart();
    }

    /**
     * Saves the cart.
     */
    protected function saveCart(): void
    {
        $this->cartHelper->getCartProvider()->saveCart();
    }

    /**
     * Returns the current (logged in) customer.
     */
    protected function getCustomer(): ?CustomerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->customerProvider->getCustomer();
    }

    /**
     * Returns the current (logged in) user.
     */
    protected function getUser(): ?UserInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->userProvider->getUser();
    }

    /**
     * Returns the cartHelper.
     */
    protected function getCartHelper(): CartHelper
    {
        return $this->cartHelper;
    }

    /**
     * Returns the sale helper.
     */
    protected function getSaleHelper(): SaleHelper
    {
        return $this->cartHelper->getSaleHelper();
    }

    /**
     * Returns the form factory.
     *
     * @deprecated
     */
    protected function getFormFactory(): FormFactoryInterface
    {
        return $this->getSaleHelper()->getFormFactory();
    }
}
