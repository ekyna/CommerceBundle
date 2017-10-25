<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber extends BaseSubscriber
{
    /**
     * @var SubjectHelper
     */
    protected $subjectHelper;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    /**
     * Sets the subject helper.
     *
     * @param SubjectHelper $subjectHelper
     */
    public function setSubjectHelper(SubjectHelper $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Sets the user provider.
     *
     * @param UserProviderInterface $provider
     */
    public function setUserProvider(UserProviderInterface $provider)
    {
        $this->userProvider = $provider;
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

        /** @var \Ekyna\Bundle\CommerceBundle\Entity\Order $order */
        $order = $this->getSaleFromEvent($event);

        // Set in charge user
        if (null === $user = $this->userProvider->getUser()) {
            return;
        }
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }
        $order->setInCharge($user);
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
                'ekyna_commerce.order.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleInsert(SaleInterface $sale)
    {
        $changed = parent::handleInsert($sale);

        $changed |= $this->updateInCharge($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale)
    {
        $changed = parent::handleUpdate($sale);

        $changed |= $this->updateInCharge($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleContentChange(SaleInterface $sale)
    {
        $changed = parent::handleContentChange($sale);

        $tags = $sale->getItemsTags();

        foreach ($sale->getItems() as $item) {
            $changed |= $this->mergeItemTags($item, $tags);
        }

        return $changed;
    }

    /**
     * Updates the order in charge field.
     *
     * @param OrderInterface $order
     *
     * @return bool
     */
    private function updateInCharge(OrderInterface $order)
    {
        if (null !== $order->getInCharge()) {
            return false;
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if (null === $customer = $order->getCustomer()) {
            return false;
        }

        if (null === $inCharge = $customer->getInCharge()) {
            return false;
        }

        $order->setInCharge($inCharge);

        return true;
    }


    /**
     * Resolves the item tags.
     *
     * @param OrderItemInterface $item
     * @param Collection    $tags
     *
     * @return bool Whether the order items tags has change.
     */
    private function mergeItemTags(OrderItemInterface $item, Collection $tags)
    {
        $changed = false;
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $changed |= $this->mergeItemTags($child, $tags);
            }
        }

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return $changed;
        }

        if (!$subject instanceof TagsSubjectInterface) {
            return $changed;
        }

        foreach ($subject->getTags() as $tag) {
            if (!$tags->contains($tag)) {
                $tags->add($tag);
                $changed = true;
            }
        }

        return $changed;
    }
}
