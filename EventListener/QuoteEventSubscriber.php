<?php

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
    /**
     * @var InChargeResolver
     */
    protected $inChargeResolver;


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
    public function onInitialize(ResourceEventInterface $event)
    {
        parent::onInitialize($event);

        /** @var QuoteInterface $sale */
        $sale = $this->getSaleFromEvent($event);

        $this->inChargeResolver->update($sale);
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

    /**
     * @inheritDoc
     *
     * @param QuoteInterface $sale
     */
    protected function handleInsert(SaleInterface $sale)
    {
        $changed = parent::handleInsert($sale);

        $changed |= $this->inChargeResolver->update($sale);

        return $changed;
    }

    /**
     * @inheritDoc
     *
     * @param QuoteInterface $sale
     */
    protected function handleUpdate(SaleInterface $sale)
    {
        $changed = parent::handleUpdate($sale);

        $changed |= $this->inChargeResolver->update($sale);

        return $changed;
    }
}
