<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Common\Transformer\SaleTransformer as BaseTransformer;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class SaleTransformer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer extends BaseTransformer
{
    /**
     * @inheritDoc
     */
    protected function preTransform()
    {
        parent::preTransform();

        // Order specific
        if ($this->target instanceof OrderInterface) {
            // If target sale has no origin customer
            if (null === $this->target->getOriginCustomer()) {
                // If source sale has customer
                if (null !== $customer = $this->source->getCustomer()) {
                    // If the source sale's origin customer is different from the target sale's customer
                    if ($customer !== $this->target->getCustomer()) {
                        // Set origin customer
                        $this->target->setOriginCustomer($customer);
                    }
                }
            }
        }
    }
}
