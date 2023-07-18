<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\TaggedSaleInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Exception\PdfException;

/**
 * Class SaleTransformSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformListener
{
    protected GeneratorInterface     $orderNumberGenerator;
    protected GeneratorInterface     $quoteNumberGenerator;
    protected DocumentGenerator      $generator;
    protected EntityManagerInterface $manager;

    public function __construct(
        GeneratorInterface     $orderNumberGenerator,
        GeneratorInterface     $quoteNumberGenerator,
        DocumentGenerator      $generator,
        EntityManagerInterface $manager
    ) {
        $this->orderNumberGenerator = $orderNumberGenerator;
        $this->quoteNumberGenerator = $quoteNumberGenerator;
        $this->generator = $generator;
        $this->manager = $manager;
    }

    public function onPostCopy(SaleTransformEvent $event): void
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

    public function onPreTransform(SaleTransformEvent $event): void
    {
        $target = $event->getTarget();

        if ($target instanceof OrderInterface) {
            // Number is needed for document filename, but may not have been generated
            if (empty($target->getNumber())) {
                $target->setNumber($this->orderNumberGenerator->generate($target));
            }

            $this->generateOrderConfirmation($target);

            return;
        }

        if ($target instanceof QuoteInterface) {
            // Number is needed for document filename, but may not have been generated
            if (empty($target->getNumber())) {
                $target->setNumber($this->quoteNumberGenerator->generate($target));
            }

            $this->generateQuoteForm($target);
        }
    }

    /**
     * Generates the order confirmation.
     */
    protected function generateOrderConfirmation(OrderInterface $order): void
    {
        $available = DocumentUtil::getSaleEditableDocumentTypes($order);
        if (!in_array(DocumentTypes::TYPE_CONFIRMATION, $available, true)) {
            return;
        }

        try {
            $attachment = $this->generator->generate($order, DocumentTypes::TYPE_CONFIRMATION);
            $this->manager->persist($attachment);
        } catch (PdfException) {
            // Fail silently for now
            // TODO Warn the admin / customer
        }
    }

    /**
     * Generates the quote confirmation.
     */
    protected function generateQuoteForm(QuoteInterface $quote): void
    {
        $available = DocumentUtil::getSaleEditableDocumentTypes($quote);
        if (!in_array(DocumentTypes::TYPE_QUOTE, $available, true)) {
            return;
        }

        try {
            $attachment = $this->generator->generate($quote, DocumentTypes::TYPE_QUOTE);
            $this->manager->persist($attachment);
        } catch (PdfException) {
            // Fail silently for now
            // TODO Warn the admin / customer
        }
    }
}
