<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Subject;

use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Bundle\CoreBundle\Modal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param Modal\Renderer $modalRenderer
     * @param CartHelper     $cartHelper
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
     * @return Response
     */
    public function addToCart(Request $request): Response
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

        // Default modal
        $modal = new Modal\Modal();
        $modal->setTitle((string)$subject);

        // Initialize event
        $event = $this->cartHelper->initializeAddToCart($subject, $modal);
        if (null !== $response = $event->getResponse()) {
            return $response;
        }

        $form = $this->cartHelper->createAddSubjectToCartForm($subject, [
            'extended'      => (bool)$request->query->get('ex', 1),
            'submit_button' => (bool)$request->query->get('sb', 0),
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
                    'label'    => 'ekyna_commerce.cart.button.add',
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

        return $this->modalRenderer->render($modal);
    }
}
