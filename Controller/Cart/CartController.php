<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout as CheckoutType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale as SaleType;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Commerce\Common\Helper\CouponHelper;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Features;
use League\Flysystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_replace;

/**
 * Class CartController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Split
 */
class CartController extends AbstractController
{
    private CouponHelper $couponSetter;
    private ModalRenderer $modalRenderer;
    private Filesystem $fileSystem;

    public function __construct(
        CouponHelper  $couponSetter,
        ModalRenderer $modalRenderer,
        Filesystem    $fileSystem
    ) {
        $this->couponSetter = $couponSetter;
        $this->modalRenderer = $modalRenderer;
        $this->fileSystem = $fileSystem;
    }

    public function couponSet(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (!$this->features->isEnabled(Features::COUPON)) {
            throw new LogicException('Coupon feature is disabled');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $form = $this->createCouponForm($cart);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->couponSetter->set($cart, $form->get('code')->getData());

                $this->getCartHelper()->getCartProvider()->saveCart();

                $form = null;
            } catch (CommerceExceptionInterface $e) {
                $form->get('code')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->buildXhrCartViewResponse(null, $form);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws LogicException
     */
    public function couponClear(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (!$this->features->isEnabled(Features::COUPON)) {
            throw new LogicException('Coupon feature is disabled');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $this->couponSetter->clear($cart);

        $this->getCartHelper()->getCartProvider()->saveCart();

        return $this->buildXhrCartViewResponse();
    }

    /**
     * Cart item (Re)configure action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function itemConfigure(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $itemId = $request->attributes->getInt('itemId');
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        $saleHelper = $this->getSaleHelper();

        if (null === $item = $saleHelper->findItemById($cart, $itemId)) {
            throw new NotFoundHttpException('Item not found.');
        }
        if (!$item->isConfigurable()) {
            throw new NotFoundHttpException('Item is not configurable.');
        }
        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        $form = $this
            ->getFormFactory()
            ->create(SaleType\SaleItemConfigureType::class, $item, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_item_configure', [
                    'itemId' => $item->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use operator to update item (cart will be automatically saved)
            $this->getCartHelper()->getCartProvider()->saveCart();

            return $this->buildXhrCartViewResponse();
        }

        $modal = $this->createModal('sale.button.item.configure')
            ->setForm($form->createView())
            ->setCondensed(true);

        return $this->modalRenderer->render($modal);
    }

    /**
     * Cart item remove action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function itemRemove(Request $request): Response
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $itemId = $request->attributes->getInt('itemId');
        if (0 < $itemId) {
            // TODO use operator to delete item (cart will be automatically saved)
            if ($this->getSaleHelper()->removeItemById($cart, $itemId)) {
                if ($cart->hasItems()) {
                    $this->getCartHelper()->getCartProvider()->saveCart();
                } else {
                    $this->getCartHelper()->getCartProvider()->clearCart();
                }
            } /*else {
                // TODO Warn about immutable item ?
            }*/
        } else {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->buildXhrCartViewResponse();
        }

        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
    }

    public function itemAdjustmentRemove(): Response
    {
        throw new \Exception('Not yet implemented.'); // TODO
    }

    public function adjustmentRemove(): Response
    {
        throw new \Exception('Not yet implemented.'); // TODO
    }

    /**
     * Attachment add action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentAdd(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $saleHelper = $this->getSaleHelper();

        $attachment = $saleHelper->getFactoryHelper()->createAttachmentForSale($cart);
        $cart->addAttachment($attachment);

        $form = $this
            ->getFormFactory()
            ->create(CheckoutType\AttachmentType::class, $attachment, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_attachment_add'),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
                'validation_groups' => ['Checkout', 'Default'],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use operator to create attachment (cart will be automatically saved)
            $this->getCartHelper()->getCartProvider()->saveCart();

            return $this->buildXhrCartViewResponse();
        }

        $modal = $this->createModal('checkout.button.attachment_add')->setForm($form->createView());

        return $this->modalRenderer->render($modal);
    }

    /**
     * Cart attachment remove action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentRemove(Request $request): Response
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $attachmentId = $request->attributes->getInt('attachmentId');
        if (0 < $attachmentId) {
            // TODO use operator to delete attachment (cart will be automatically saved)
            if ($this->getSaleHelper()->removeAttachmentById($cart, $attachmentId)) {
                if ($cart->hasItems()) {
                    $this->getCartHelper()->getCartProvider()->saveCart();
                } else {
                    $this->getCartHelper()->getCartProvider()->clearCart();
                }
            } /*else {
                // TODO Warn about internal attachment ?
            }*/
        } else {
            throw new NotFoundHttpException('Unexpected attachment identifier.');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->buildXhrCartViewResponse();
        }

        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_index'));
    }


    /**
     * Sale attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function attachmentDownload(Request $request): Response
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $attachment = null;
        $attachmentId = $request->attributes->getInt('attachmentId');
        if (0 < $attachmentId) {
            $attachment = $this->getSaleHelper()->findAttachmentById($cart, $attachmentId);
        }
        if (null === $attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        $helper = new FilesystemHelper($this->fileSystem);

        if (!$helper->fileExists($attachment->getPath(), false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $helper->createFileResponse($attachment->getPath());
    }

    /**
     * Edit information action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function information(Request $request): Response
    {
        return $this->handleForm(
            $request,
            CheckoutType\InformationType::class,
            'checkout.button.edit_information',
            'ekyna_commerce_cart_information',
            [
                'validation_groups' => ['Default', 'Identity'],
            ]
        );
    }

    /**
     * Edit invoice address action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function invoiceAddress(Request $request): Response
    {
        return $this->handleForm(
            $request,
            SaleType\SaleAddressType::class,
            'checkout.button.edit_invoice',
            'ekyna_commerce_cart_invoice_address',
            [
                'address_type'      => CartAddressType::class,
                'validation_groups' => ['Default', 'Address'],
            ]
        );
    }

    /**
     * Edit delivery address action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deliveryAddress(Request $request): Response
    {
        return $this->handleForm(
            $request,
            SaleType\SaleAddressType::class,
            'checkout.button.edit_delivery',
            'ekyna_commerce_cart_delivery_address',
            [
                'address_type'      => CartAddressType::class,
                'validation_groups' => ['Default', 'Address'],
                'delivery'          => true,
            ]
        );
    }

    /**
     * Edit comment action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function comment(Request $request): Response
    {
        return $this->handleForm(
            $request,
            CheckoutType\CommentType::class,
            'checkout.button.edit_comment',
            'ekyna_commerce_cart_comment',
            [
                'validation_groups' => ['Checkout', 'Default'],
            ]
        );
    }

    /**
     * Handle form.
     *
     * @param Request $request
     * @param string  $type
     * @param string  $title
     * @param string  $route
     * @param array   $options
     *
     * @return Response
     */
    public function handleForm(
        Request $request,
        string  $type,
        string  $title,
        string  $route,
        array   $options = []
    ): Response {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
        }

        if ($cart->isLocked()) {
            throw new AccessDeniedHttpException('Cart is locked for payment.');
        }

        $form = $this
            ->getFormFactory()
            ->create($type, $cart, array_replace([
                'method' => 'post',
                'action' => $this->generateUrl($route),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ], $options));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getCartHelper()->getCartProvider()->saveCart();

            return $this->buildXhrCartViewResponse();
        }

        $modal = $this->createModal($title)->setForm($form->createView());

        return $this->modalRenderer->render($modal);
    }

    /**
     * Creates a modal.
     */
    protected function createModal(string $title, array $buttons = []): Modal
    {
        if (empty($buttons)) {
            $buttons = [
                array_replace(Modal::BTN_SUBMIT, [
                    'label' => 'button.save',
                ]),
                Modal::BTN_CANCEL,
            ];
        }

        return (new Modal($title))
            ->setDomain('EkynaCommerce')
            ->setButtons($buttons);
    }

    /**
     * Returns the XHR cart view response.
     */
    protected function buildXhrCartViewResponse(
        FormInterface $quantities = null,
        FormInterface $coupon = null
    ): Response {
        $parameters = [];

        // Cart
        $parameters['cart'] = $cart = $this->getCart();

        if (null !== $cart) {
            $view = $this->getCartHelper()->buildView($cart);

            $view->vars['quantities_form'] = $quantities
                ? $quantities->createView()
                : $this->createQuantitiesForm($cart)->createView();

            if ($this->features->isEnabled(Features::COUPON)) {
                $view->vars['coupon_form'] = $coupon
                    ? $coupon->createView()
                    : $this->createCouponForm($cart)->createView();
            }

            $parameters['view'] = $view;
        }

        $parameters['controls'] = $this->buildCartControls($cart);

        $response = $this->render('@EkynaCommerce/Cart/response.xml.twig', $parameters);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }
}
