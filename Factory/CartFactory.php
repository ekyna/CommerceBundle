<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use DateTime;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class CartFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method CartInterface create(bool $initialize = true)
 * @method CartInterface createWithCustomer(CustomerInterface $customer)
 */
class CartFactory extends AbstractSaleFactory
{
    protected string $expirationDelay;

    public function setExpirationDelay(string $delay): void
    {
        $this->expirationDelay = $delay;
    }

    public function initialize(SaleInterface $sale): void
    {
        parent::initialize($sale);

        if (!$sale instanceof CartInterface) {
            throw new UnexpectedTypeException($sale, CartInterface::class);
        }

        $date = new DateTime();
        $date->modify($this->expirationDelay);
        $sale->setExpiresAt($date);
    }
}
