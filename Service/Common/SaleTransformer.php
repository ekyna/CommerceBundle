<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformer as BaseTransformer;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Document\Util\SaleDocumentUtil;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;

/**
 * Class SaleTransformer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer extends BaseTransformer
{
    /**
     * @var DocumentGenerator
     */
    protected $documentGenerator;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    /**
     * Sets the document generator.
     *
     * @param DocumentGenerator $generator
     */
    public function setDocumentGenerator(DocumentGenerator $generator)
    {
        $this->documentGenerator = $generator;
    }

    /**
     * Sets the entity manager.
     *
     * @param EntityManagerInterface $manager
     */
    public function setEntityManager(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function postTransform()
    {
        parent::postTransform();

        if ($this->target instanceof OrderInterface) {
            $this->generateOrderConfirmation($this->target);

            return;
        }

        if ($this->target instanceof QuoteInterface) {
            $this->generateQuoteForm($this->target);

            return;
        }
    }

    /**
     * Generates the order confirmation.
     *
     * @param OrderInterface $order
     */
    protected function generateOrderConfirmation(OrderInterface $order)
    {
        $confirmedStates = [
            OrderStates::STATE_COMPLETED,
            OrderStates::STATE_ACCEPTED,
            OrderStates::STATE_PENDING,
        ];
        if (!in_array($order->getState(), $confirmedStates, true)) {
            return;
        }

        $available = SaleDocumentUtil::getSaleEditableDocumentTypes($order);
        if (!in_array(DocumentTypes::TYPE_CONFIRMATION, $available, true)) {
            return;
        }

        $attachment = $this->documentGenerator->generate($order, DocumentTypes::TYPE_CONFIRMATION);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();
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

        $attachment = $this->documentGenerator->generate($quote, DocumentTypes::TYPE_QUOTE);

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();
    }
}
