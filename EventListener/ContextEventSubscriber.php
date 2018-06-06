<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Common\Event\ContextEvent;
use Ekyna\Component\Commerce\Common\Event\ContextEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ContextEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorisationChecker;


    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorisationChecker
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorisationChecker
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorisationChecker = $authorisationChecker;
    }

    /**
     * Context build event handler.
     *
     * @param ContextEvent $event
     */
    public function onContextBuild(ContextEvent $event)
    {
        $context = $event->getContext();

        if (null === $this->tokenStorage->getToken()) {
            return;
        }

        if (!$this->authorisationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $context->setAdmin(true);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ContextEvents::BUILD => ['onContextBuild'],
        ];
    }
}
