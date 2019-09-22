<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout as CheckoutType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale as SaleType;
use Ekyna\Bundle\CoreBundle\Modal;
use Ekyna\Component\Commerce\Common\Helper\CouponHelper;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Features;
use League\Flysystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CartController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartController extends AbstractController
{
    /**
     * @var CouponHelper
     */
    private $couponSetter;

    /**
     * @var Modal\Renderer
     */
    private $modalRenderer;

    /**
     * @var Filesystem
     */
    private $fileSystem;


    /**
     * Constructor.
     *
     * @param CouponHelper   $couponSetter
     * @param Modal\Renderer $modalRenderer
     * @param Filesystem     $fileSystem
     */
    public function __construct(
        CouponHelper $couponSetter,
        Modal\Renderer $modalRenderer,
        Filesystem $fileSystem
    ) {
        $this->couponSetter = $couponSetter;
        $this->modalRenderer = $modalRenderer;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Coupon set action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function couponSet(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (!$this->features->isEnabled(Features::COUPON)) {
            throw new LogicException("Coupon feature is disabled");
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
            throw new LogicException("Coupon feature is disabled");
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

        $itemId = intval($request->attributes->get('itemId'));
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

        $modal = $this->createModal('ekyna_commerce.sale.button.item.configure', $form->createView());
        $modal->setCondensed(true);

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

        $itemId = intval($request->attributes->get('itemId'));
        if (0 < $itemId) {
            // TODO use operator to delete item (cart will be automatically saved)
            if ($this->getSaleHelper()->removeItemById($cart, $itemId)) {
                if ($cart->hasItems()) {
                    $this->getCartHelper()->getCartProvider()->saveCart();
                } else {
                    $this->getCartHelper()->getCartProvider()->clearCart();
                }
            } else {
                // TODO Warn about immutable item ?
            }
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

        $attachment = $saleHelper->getSaleFactory()->createAttachmentForSale($cart);
        $cart->addAttachment($attachment);

        $form = $this
            ->getFormFactory()
            ->create(CheckoutType\AttachmentType::class, $attachment, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_attachment_add'),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use operator to create attachment (cart will be automatically saved)
            $this->getCartHelper()->getCartProvider()->saveCart();

            return $this->buildXhrCartViewResponse();
        }

        $modal = $this->createModal('ekyna_commerce.checkout.button.attachment_add', $form->createView());

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

        $attachmentId = intval($request->attributes->get('attachmentId'));
        if (0 < $attachmentId) {
            // TODO use operator to delete attachment (cart will be automatically saved)
            if ($this->getSaleHelper()->removeAttachmentById($cart, $attachmentId)) {
                if ($cart->hasItems()) {
                    $this->getCartHelper()->getCartProvider()->saveCart();
                } else {
                    $this->getCartHelper()->getCartProvider()->clearCart();
                }
            } else {
                // TODO Warn about internal attachment ?
            }
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
        $attachmentId = intval($request->attributes->get('attachmentId'));
        if (0 < $attachmentId) {
            $attachment = $this->getSaleHelper()->findAttachmentById($cart, $attachmentId);
        }
        if (null === $attachment) {
            throw new NotFoundHttpException('Attachment not found.');
        }

        if (!$this->fileSystem->has($attachment->getPath())) {
            throw new NotFoundHttpException('File not found');
        }
        $file = $this->fileSystem->get($attachment->getPath());

        $response = new Response($file->read());
        $response->setPrivate();

        $response->headers->set('Content-Type', $file->getMimetype());
        $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $attachment->guessFilename()
        );

        return $response;
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
            'ekyna_commerce.checkout.button.edit_information',
            'ekyna_commerce_cart_information',
            [
                'validation_groups' => ['Identity'],
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
            'ekyna_commerce.checkout.button.edit_invoice',
            'ekyna_commerce_cart_invoice_address',
            [
                'address_type'      => CartAddressType::class,
                'validation_groups' => ['Address'],
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
            'ekyna_commerce.checkout.button.edit_delivery',
            'ekyna_commerce_cart_delivery_address',
            [
                'address_type'      => CartAddressType::class,
                'validation_groups' => ['Address'],
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
            'ekyna_commerce.checkout.button.edit_comment',
            'ekyna_commerce_cart_comment',
            [
                'validation_groups' => ['Comment'],
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
    public function handleForm(Request $request, $type, $title, $route, array $options = []): Response
    {
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

        $modal = $this->createModal($title, $form->createView());

        return $this->modalRenderer->render($modal);
    }

    /**
     * Creates a modal.
     *
     * @param string $title
     * @param mixed  $content
     * @param array  $buttons
     *
     * @return Modal\Modal
     */
    protected function createModal($title, $content = null, $buttons = []): Modal\Modal
    {
        if (empty($buttons)) {
            $buttons = [
                [
                    'id'       => 'submit',
                    'label'    => 'ekyna_core.button.save',
                    'icon'     => 'glyphicon glyphicon-ok',
                    'cssClass' => 'btn-success',
                    'autospin' => true,
                ],
                [
                    'id'       => 'close',
                    'label'    => 'ekyna_core.button.cancel',
                    'icon'     => 'glyphicon glyphicon-remove',
                    'cssClass' => 'btn-default',
                ],
            ];
        }

        return new Modal\Modal($title, $content, $buttons);
    }

    /**
     * Returns the XHR cart view response.
     *
     * @param FormInterface $quantities
     * @param FormInterface $coupon
     *
     * @return Response
     */
    protected function buildXhrCartViewResponse(
        FormInterface $quantities = null,
        FormInterface $coupon = null
    ): Response {
        $parameters = [];

        // Cart
        $parameters['cart'] = $cart = $this->getCart();

        if (null !== $cart) {
            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);

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
