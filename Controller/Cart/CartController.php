<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Checkout as CheckoutType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale as SaleType;
use Ekyna\Bundle\CoreBundle\Modal;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CartController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartController extends AbstractController
{
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
     * @param Modal\Renderer $modalRenderer
     * @param Filesystem     $fileSystem
     */
    public function __construct(Modal\Renderer $modalRenderer, Filesystem $fileSystem)
    {
        $this->modalRenderer = $modalRenderer;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Cart widget action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse($this->generateUrl('ekyna_commerce_cart_checkout_index'));
        }

        $response = $this->render('EkynaCommerceBundle:Cart:widget.xml.twig', [
            'cart' => $this->getCart(),
        ]);

        $response->headers->set('Content-Type', 'application/xml');

        return $response->setPrivate();
    }

    /**
     * Cart item (Re)configure action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function itemConfigureAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function itemRemoveAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
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

    public function itemAdjustmentRemoveAction()
    {
        throw new \Exception('Not yet implemented.'); // TODO
    }

    public function adjustmentRemoveAction()
    {
        throw new \Exception('Not yet implemented.'); // TODO
    }

    /**
     * Attachment add action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function attachmentAddAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function attachmentRemoveAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function attachmentDownloadAction(Request $request)
    {
        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function informationAction(Request $request)
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoiceAddressAction(Request $request)
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deliveryAddressAction(Request $request)
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function commentAction(Request $request)
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleForm(Request $request, $type, $title, $route, array $options = [])
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        if (null === $cart = $this->getCart()) {
            throw new NotFoundHttpException('Cart not found.');
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
    protected function createModal($title, $content = null, $buttons = [])
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildXhrCartViewResponse()
    {
        $parameters = [];

        // Cart
        $parameters['cart'] = $cart = $this->getCart();

        if (null !== $cart) {
            $saleHelper = $this->getSaleHelper();

            $form = $saleHelper->createQuantitiesForm($cart, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_cart_checkout_index'),
            ]);

            $view = $this->getCartHelper()->buildView($cart, ['editable' => true]);
            $view->vars['form'] = $form->createView();

            $parameters['view'] = $view;
        }

        $parameters['controls'] = $this->buildCartControls($cart);

        $response = $this->render('EkynaCommerceBundle:Cart:response.xml.twig', $parameters);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }
}
