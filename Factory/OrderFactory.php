<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class OrderFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface create()
 * @method OrderInterface createWithCustomer(CustomerInterface $customer)
 */
class OrderFactory extends AbstractSaleFactory
{
    protected InChargeResolver $inChargeResolver;

    public function setInChargeResolver(InChargeResolver $resolver): void
    {
        $this->inChargeResolver = $resolver;
    }

    protected function initialize(SaleInterface $sale): void
    {
        parent::initialize($sale);

        if (!$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
        }

        $this->inChargeResolver->update($sale);
    }
}
