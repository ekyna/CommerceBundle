<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\OrderEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
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
     * @var SubjectHelper
     */
    protected $subjectHelper;


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
     * @inheritDoc
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        parent::onContentChange($event);

        if ($event->isPropagationStopped()) {
            return;
        }

        /** @var OrderInterface $sale */
        $sale = $this->getSaleFromEvent($event);

        $tags = $sale->getItemsTags();

        $changed = false;
        foreach ($sale->getItems() as $item) {
            $changed |= $this->mergeItemTags($item, $tags);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($sale, false);
        }
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
}
