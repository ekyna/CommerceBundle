<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CoreBundle\Modal;
use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AddToCartEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     * @param TranslatorInterface    $translator
     * @param UrlGeneratorInterface  $urlGenerator
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
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
            ->setSize(Modal\Modal::SIZE_NORMAL)
            ->setType(Modal\Modal::TYPE_SUCCESS)
            ->setButtons([
                [
                    'id'       => 'close',
                    'label'    => 'ekyna_commerce.add_to_cart.button.continue',
                    'icon'     => 'glyphicon glyphicon-arrow-left',
                    'cssClass' => 'btn-default',
                ],
                [
                    'id'       => 'cart',
                    'label'    => 'ekyna_commerce.add_to_cart.button.cart',
                    'icon'     => 'glyphicon glyphicon-shopping-cart',
                    'cssClass' => 'btn-primary',
                    'autospin' => true,
                    'action'   => 'function() {window.location.href="' . $cartPath . '";}',
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

        $message = $this->translator->trans('ekyna_commerce.add_to_cart.message.success', [
            '%designation%' => (string)$subject,
            '%subject_url%' => $this->subjectHelper->generatePublicUrl($subject),
            '%cart_url%'    => $cartPath,
        ]);

        $event
            ->setMessage($message)
            ->stopPropagation();

        if (null === $modal = $event->getModal()) {
            return;
        }

        $modal->setContent($message);
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
            ->setSize(Modal\Modal::SIZE_NORMAL)
            ->setType(Modal\Modal::TYPE_DANGER)
            ->setButtons([
                [
                    'id'       => 'close',
                    'label'    => 'ekyna_core.button.close',
                    'icon'     => 'glyphicon glyphicon-remove',
                    'cssClass' => 'btn-default',
                ],
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

        $message = $this->translator->trans('ekyna_commerce.add_to_cart.message.failure', [
            '%designation%' => (string)$subject,
            '%subject_url%' => $this->subjectHelper->generatePublicUrl($subject),
        ]);

        $event
            ->setMessage($message)
            ->stopPropagation();

        if (null === $modal = $event->getModal()) {
            return;
        }

        $modal->setContent($message);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
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