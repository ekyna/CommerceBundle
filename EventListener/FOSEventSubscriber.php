<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class FOSEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FOSEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Change password success event handler.
     *
     * @param FormEvent $event
     */
    public function onChangePasswordSuccess(FormEvent $event)
    {
        $this->setRedirectResponse($event);
    }

    /**
     * Resetting reset success event handler.
     *
     * @param FormEvent $event
     */
    public function onResettingResetSuccess(FormEvent $event)
    {
        $this->setRedirectResponse($event);
    }

    /**
     * Sets the redirect response.
     *
     * @param FormEvent $event
     */
    private function setRedirectResponse(FormEvent $event)
    {
        $url = $this->urlGenerator->generate('ekyna_commerce_account_information_index');

        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => ['onChangePasswordSuccess', 0],
            FOSUserEvents::RESETTING_RESET_SUCCESS => ['onResettingResetSuccess', 0],
        ];
    }
}
