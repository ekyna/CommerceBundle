<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber extends BaseSubscriber
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var InChargeResolver
     */
    protected $inChargeResolver;


    /**
     * Sets the subject helper.
     *
     * @param SubjectHelperInterface $subjectHelper
     */
    public function setSubjectHelper(SubjectHelperInterface $subjectHelper)
    {
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * Sets the 'in charge' resolver.
     *
     * @param InChargeResolver $inChargeResolver
     */
    public function setInChargeResolver(InChargeResolver $inChargeResolver)
    {
        $this->inChargeResolver = $inChargeResolver;
    }

    /**
     * Initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event): void
    {
        parent::onInitialize($event);

        /** @var OrderInterface $sale */
        $sale = $this->getSaleFromEvent($event);

        $this->inChargeResolver->update($sale);
    }

    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event): void
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
     * @inheritdoc
     */
    public function onPreUpdate(ResourceEventInterface $event): void
    {
        try {
            parent::onPreUpdate($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage($e->getMessage(), ResourceMessage::TYPE_ERROR));
        }
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleInsert(SaleInterface $sale): bool
    {
        $changed = parent::handleInsert($sale);

        $changed |= $this->inChargeResolver->update($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale): bool
    {
        $changed = parent::handleUpdate($sale);

        $changed |= $this->inChargeResolver->update($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param OrderInterface $sale
     */
    protected function handleContentChange(SaleInterface $sale): bool
    {
        $changed = parent::handleContentChange($sale);

        $tags = $sale->getItemsTags();

        // TODO Remove unexpected tags

        foreach ($sale->getItems() as $item) {
            $changed |= $this->mergeItemTags($item, $tags);
        }

        return $changed;
    }

    /**
     * Resolves the item tags.
     *
     * @param OrderItemInterface $item
     * @param Collection    $tags
     *
     * @return bool Whether the order items tags has change.
     */
    private function mergeItemTags(OrderItemInterface $item, Collection $tags): bool
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
