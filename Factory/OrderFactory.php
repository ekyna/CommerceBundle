<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class OrderFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface create(bool $initialize = true)
 * @method OrderInterface createWithCustomer(CustomerInterface $customer)
 */
class OrderFactory extends AbstractSaleFactory
{
    protected InChargeResolver $inChargeResolver;

    public function setInChargeResolver(InChargeResolver $resolver): void
    {
        $this->inChargeResolver = $resolver;
    }

    public function initialize(SaleInterface $sale): void
    {
        parent::initialize($sale);

        if (!$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
        }

        $this->inChargeResolver->update($sale);
    }
}
