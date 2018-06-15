<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\TaggedSaleInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Document\Util\SaleDocumentUtil;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SaleTransformSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformSubscriber implements EventSubscriberInterface
{
    /**
     * @var NumberGeneratorInterface
     */
    protected $orderNumberGenerator;

    /**
     * @var NumberGeneratorInterface
     */
    protected $quoteNumberGenerator;

    /**
     * @var DocumentGenerator
     */
    protected $generator;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $orderNumberGenerator
     * @param NumberGeneratorInterface $quoteNumberGenerator
     * @param DocumentGenerator        $generator
     * @param EntityManagerInterface   $manager
     */
    public function __construct(
        NumberGeneratorInterface $orderNumberGenerator,
        NumberGeneratorInterface $quoteNumberGenerator,
        DocumentGenerator $generator,
        EntityManagerInterface $manager
    ) {
        $this->orderNumberGenerator = $orderNumberGenerator;
        $this->quoteNumberGenerator = $quoteNumberGenerator;
        $this->generator = $generator;
        $this->manager = $manager;
    }

    /**
     * Pre copy event handler.
     *
     * @param SaleTransformEvent $event
     */
    public function onPreCopy(SaleTransformEvent $event)
    {
    }

    /**
     * Post copy event handler.
     *
     * @param SaleTransformEvent $event
     */
    public function onPostCopy(SaleTransformEvent $event)
    {
        $source = $event->getSource();
        $target = $event->getTarget();

        if ($source instanceof TaggedSaleInterface && $target instanceof TaggedSaleInterface) {
            foreach ($source->getTags() as $tag) {
                $target->addTag($tag);
            }
            foreach ($source->getItemsTags() as $tag) {
                $target->addItemsTag($tag);
            }
        }
    }

    /**
     * Pre transform event handler.
     *
     * @param SaleTransformEvent $event
     */
    public function onPreTransform(SaleTransformEvent $event)
    {
        $target = $event->getTarget();

        if ($target instanceof OrderInterface) {
            // Number is needed for document filename, but may not have been generated
            $this->orderNumberGenerator->generate($target);

            $this->generateOrderConfirmation($target);

            return;
        }

        if ($target instanceof QuoteInterface) {
            // Number is needed for document filename, but may not have been generated
            $this->quoteNumberGenerator->generate($target);

            $this->generateQuoteForm($target);

            return;
        }
    }

    /**
     * Post transform event handler.
     *
     * @param SaleTransformEvent $event
     */
    public function onPostTransform(SaleTransformEvent $event)
    {
    }

    /**
     * Generates the order confirmation.
     *
     * @param OrderInterface $order
     */
    protected function generateOrderConfirmation(OrderInterface $order)
    {
        $available = SaleDocumentUtil::getSaleEditableDocumentTypes($order);
        if (!in_array(DocumentTypes::TYPE_CONFIRMATION, $available, true)) {
            return;
        }

        // TODO Catch generator exception ?
        $attachment = $this->generator->generate($order, DocumentTypes::TYPE_CONFIRMATION);

        $this->manager->persist($attachment);
    }

    /**
     * Generates the quote confirmation.
     *
     * @param QuoteInterface $quote
     */
    protected function generateQuoteForm(QuoteInterface $quote)
    {
        $available = SaleDocumentUtil::getSaleEditableDocumentTypes($quote);
        if (!in_array(DocumentTypes::TYPE_QUOTE, $available, true)) {
            return;
        }

        // TODO Catch generator exception ?
        $attachment = $this->generator->generate($quote, DocumentTypes::TYPE_QUOTE);

        $this->manager->persist($attachment);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleTransformEvents::PRE_COPY       => 'onPreCopy',
            SaleTransformEvents::POST_COPY      => 'onPostCopy',
            SaleTransformEvents::PRE_TRANSFORM  => 'onPreTransform',
            SaleTransformEvents::POST_TRANSFORM => 'onPostTransform',
        ];
    }
}
