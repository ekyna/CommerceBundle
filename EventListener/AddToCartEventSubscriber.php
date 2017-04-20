<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AddToCartEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartEventSubscriber implements EventSubscriberInterface
{
    private SubjectHelperInterface $subjectHelper;
    private TranslatorInterface $translator;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        SubjectHelperInterface $subjectHelper,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $urlGenerator
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Add to cart pre success handler.
     *
     * @param AddToCartEvent $event
     */
    public function onPreSuccess(AddToCartEvent $event)
    {
        if (null === $modal = $event->getModal()) {
            return;
        }

        $cartPath = $this->urlGenerator->generate('ekyna_commerce_cart_checkout_index');

        $modal
            ->setSize(Modal::SIZE_NORMAL)
            ->setType(Modal::TYPE_SUCCESS)
            ->setButtons([
                [
                    'id'           => 'close',
                    'label'        => 'cart.button.continue',
                    'trans_domain' => 'EkynaCommerce',
                    'icon'         => 'glyphicon glyphicon-arrow-left',
                    'cssClass'     => 'btn-default',
                ],
                [
                    'id'           => 'cart',
                    'label'        => 'cart.button.cart',
                    'trans_domain' => 'EkynaCommerce',
                    'icon'         => 'glyphicon glyphicon-shopping-cart',
                    'cssClass'     => 'btn-primary',
                    'autospin'     => true,
                    'action'       => 'function() {window.location.href="' . $cartPath . '";}',
                ],
            ]);
    }

    /**
     * Add to cart success handler.
     *
     * @param AddToCartEvent $event
     */
    public function onSuccess(AddToCartEvent $event)
    {
        $subject = $event->getSubject();

        $cartPath = $this->urlGenerator->generate('ekyna_commerce_cart_checkout_index');

        $message = $this->translator->trans('cart.message.success', [
            '%designation%' => (string)$subject,
            '%subject_url%' => $this->subjectHelper->generatePublicUrl($subject),
            '%cart_url%'    => $cartPath,
        ], 'EkynaCommerce');

        $event
            ->setMessage($message)
            ->stopPropagation();

        if (null === $modal = $event->getModal()) {
            return;
        }

        $modal->setHtml($message);
    }

    /**
     * Add to cart pre failure handler.
     *
     * @param AddToCartEvent $event
     */
    public function onPreFailure(AddToCartEvent $event)
    {
        if (null === $modal = $event->getModal()) {
            return;
        }

        $modal
            ->setSize(Modal::SIZE_NORMAL)
            ->setType(Modal::TYPE_DANGER)
            ->setButtons([
                Modal::BTN_CLOSE
            ]);
    }

    /**
     * Add to cart failure handler.
     *
     * @param AddToCartEvent $event
     */
    public function onFailure(AddToCartEvent $event)
    {
        $subject = $event->getSubject();

        $message = $this->translator->trans('cart.message.failure', [
            '%designation%' => (string)$subject,
            '%subject_url%' => $this->subjectHelper->generatePublicUrl($subject),
        ], 'EkynaCommerce');

        $event
            ->setMessage($message)
            ->stopPropagation();

        if (null === $modal = $event->getModal()) {
            return;
        }

        $modal->setHtml($message);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AddToCartEvent::SUCCESS => [
                ['onPreSuccess', 2048],
                ['onSuccess', -2048],
            ],
            AddToCartEvent::FAILURE => [
                ['onPreFailure', 2048],
                ['onFailure', -2048],
            ],
        ];
    }
}
