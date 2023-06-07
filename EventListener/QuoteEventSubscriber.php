<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\QuoteEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteEventSubscriber extends BaseSubscriber
{
    use SaleTagsTrait;

    protected InChargeResolver $inChargeResolver;

    public function setInChargeResolver(InChargeResolver $inChargeResolver): void
    {
        $this->inChargeResolver = $inChargeResolver;
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface) {
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
    protected function handleContentChange(SaleInterface $sale): void
    {
        parent::handleContentChange($sale);

        $this->updateSaleItemsTags($sale);
    }
}
