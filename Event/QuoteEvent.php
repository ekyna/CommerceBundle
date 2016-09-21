<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Commerce\Quote\Model\QuoteEventInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class QuoteEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteEvent extends ResourceEvent implements QuoteEventInterface
{
    /**
     * Constructor.
     *
     * @param QuoteInterface $quote
     */
    public function __construct(QuoteInterface $quote)
    {
        $this->setResource($quote);
    }

    /**
     * @inheritdoc
     */
    public function getQuote()
    {
        return $this->getResource();
    }
}
