<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Subject;

use Ekyna\Bundle\CommerceBundle\Service\Cart\CartHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AddToCartController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartController
{
    private ModalRenderer $modalRenderer;
    private CartHelper    $cartHelper;

    public function __construct(ModalRenderer $modalRenderer, CartHelper $cartHelper)
    {
        $this->modalRenderer = $modalRenderer;
        $this->cartHelper = $cartHelper;
    }

    public function __invoke(Request $request): Response
    {
        $provider = $request->attributes->get('provider');
        $identifier = $request->attributes->getInt('identifier');

        $subject = $this->cartHelper->getSaleHelper()->getSubjectHelper()->find($provider, $identifier);

        if (null === $subject) {
            throw new NotFoundHttpException('Subject not found.');
        }

        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        // Default modal
        $modal = new Modal();
        $modal->setTitle((string)$subject);

        // Initialize event
        $event = $this->cartHelper->initializeAddToCart($subject, $modal);
        if (null !== $response = $event->getResponse()) {
            return $response;
        }

        $form = $this->cartHelper->createAddSubjectToCartForm($subject, [
            'extended'      => $request->query->getBoolean('ex', true),
            'submit_button' => $request->query->getBoolean('sb'),
        ]);

        if (null !== $event = $this->cartHelper->handleAddSubjectToCartForm($form, $request, $modal)) {
            return $event->getResponse();
        }

        // TODO form template
        $modal
            ->setSize(Modal::SIZE_WIDE)
            ->setType(Modal::TYPE_PRIMARY)
            ->setCondensed(true)
            ->setForm($form->createView())
            ->setButtons([
                array_replace(Modal::BTN_SUBMIT, [
                    'label'        => 'cart.button.add',
                    'trans_domain' => 'EkynaCommerce',
                ]),
                Modal::BTN_CLOSE,
            ]);

        return $this->modalRenderer->render($modal);
    }
}
