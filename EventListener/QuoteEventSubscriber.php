<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\QuoteEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteEventSubscriber extends BaseSubscriber
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    /**
     * Sets the user provider.
     *
     * @param UserProviderInterface $userProvider
     */
    public function setUserProvider(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Sets the authorization checker.
     *
     * @param AuthorizationCheckerInterface $checker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $checker)
    {
        $this->authorizationChecker = $checker;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        parent::onInitialize($event);

        /** @var \Ekyna\Bundle\CommerceBundle\Entity\Quote $quote */
        $quote = $this->getSaleFromEvent($event);

        // Set in charge user
        if (null === $user = $this->userProvider->getUser()) {
            return;
        }
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $quote->setInCharge($user);
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.quote.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            ));
        }
    }
}
