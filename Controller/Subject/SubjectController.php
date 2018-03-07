<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Subject;

use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Bundle\CoreBundle\Modal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SubjectController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectController
{
    /**
     * @var Modal\Renderer
     */
    private $modalRenderer;

    /**
     * @var CartHelper
     */
    private $cartHelper;


    /**
     * Constructor.
     *
     * @param Modal\Renderer           $modalRenderer
     * @param CartHelper               $cartHelper
     */
    public function __construct(Modal\Renderer $modalRenderer, CartHelper $cartHelper)
    {
        $this->modalRenderer = $modalRenderer;
        $this->cartHelper = $cartHelper;
    }

    /**
     * Subject add to cart action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addToCartAction(Request $request)
    {
        $provider = $request->attributes->get('provider');
        $identifier = $request->attributes->get('identifier');

        $subject = $this->cartHelper->getSaleHelper()->getSubjectHelper()->find($provider, $identifier);

        if (null === $subject) {
            throw new NotFoundHttpException('Subject not found.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

//        $pickTabletPath = $this->generateUrl('app_shop_pick_tablet', [
//            'next' => $this->generateUrl('app_cart_add_product', [
//                'productSlug' => $product->getSlug(),
//            ]),
//        ]);
//
//        // Redirect to pick tablet if product is not a tablet and no tablet selected
//        $context = $this->get('app.shop.user_context');
//        $tablet = $context->getTablet();
//        if (!$product->isTablet() && null === $tablet) {
//            return $this->redirect($pickTabletPath);
//        }
//
//        // Modal title
//        $title = $product->getFullTitle(true);
//        if ((null !== $tablet) && (ProductTypes::TYPE_CONFIGURABLE === $product->getType())) {
//            $title = $this->getTranslator()->trans('web.shop.title.configure', [
//                '{{product}}' => $product->getTitle(),
//                '{{tablet}}'  => $tablet->getFullTitle(true),
//            ]);
//        }

        // Default modal
        $modal = new Modal\Modal();
        $modal->setTitle((string)$subject);

        // Initialize event
        $event = $this->cartHelper->initializeAddToCart($subject, $modal);
        if (null !== $response = $event->getResponse()) {
            return $response;
        }

        // If compatible
        //if ($product->isTablet() || $this->get('app.compatibility.checker')->isCompatible($tablet, $product, true)) {

        $form = $this->cartHelper->createAddSubjectToCartForm($subject, [
            'extended' => (bool)$request->query->get('extended', 1),
        ]);

        if (null !== $event = $this->cartHelper->handleAddSubjectToCartForm($form, $request, $modal)) {
            return $event->getResponse();
        }

        // TODO form template
        $modal
            ->setSize(Modal\Modal::SIZE_WIDE)
            ->setType(Modal\Modal::TYPE_PRIMARY)
            ->setCondensed(true)
            ->setContent($form->createView())
            ->setButtons([
                [
                    'id'       => 'submit',
                    'label'    => 'ekyna_commerce.add_to_cart.button.add',
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
            ]);

        /*} else { // Not compatible
            $message = $this->getTranslator()->trans('web.shop.message.not_compatible', [
                '{{title}}' => $tablet->getFullTitle(true),
                '{{path}}'  => $pickTabletPath,
            ]);
            $this->addFlash($message, 'warning');
            $modal->setType(Modal::TYPE_WARNING);
        }*/

        return $this->modalRenderer->render($modal);
    }
}