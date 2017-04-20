<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\QuoteEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteEventSubscriber extends BaseSubscriber
{
    protected SubjectHelperInterface $subjectHelper;
    protected InChargeResolver $inChargeResolver;


    public function setSubjectHelper(SubjectHelperInterface $subjectHelper): void
    {
        $this->subjectHelper = $subjectHelper;
    }

    public function setInChargeResolver(InChargeResolver $inChargeResolver): void
    {
        $this->inChargeResolver = $inChargeResolver;
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(ResourceMessage::create(
                'quote.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            )->setDomain('EkynaCommerce'));
        }
    }

    protected function handleInsert(SaleInterface $sale): bool
    {
        $changed = parent::handleInsert($sale);

        /** @var QuoteInterface $sale */
        return $this->inChargeResolver->update($sale) || $changed;
    }

    protected function handleUpdate(SaleInterface $sale): bool
    {
        $changed = parent::handleUpdate($sale);

        /** @var QuoteInterface $sale */
        return $this->inChargeResolver->update($sale) || $changed;
    }

    /**
     * @param QuoteInterface $sale
     */
    protected function handleContentChange(SaleInterface $sale): bool
    {
        $changed = parent::handleContentChange($sale);

        $tags = $sale->getItemsTags();

        // TODO Remove unexpected tags

        foreach ($sale->getItems() as $item) {
            $changed = $this->mergeItemTags($item, $tags) || $changed;
        }

        return $changed;
    }

    /**
     * Resolves the item tags.
     *
     * @return bool Whether the order items tags has change.
     */
    private function mergeItemTags(QuoteItemInterface $item, Collection $tags): bool
    {
        $changed = false;
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $changed = $this->mergeItemTags($child, $tags) || $changed;
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
