<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class QuoteFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @method QuoteInterface create()
 * @method QuoteInterface createWithCustomer(CustomerInterface $customer)
 */
class QuoteFactory extends AbstractSaleFactory
{
    protected InChargeResolver $inChargeResolver;
    protected string           $expirationDelay;


    public function setInChargeResolver(InChargeResolver $resolver): void
    {
        $this->inChargeResolver = $resolver;
    }

    public function setExpirationDelay(string $delay): void
    {
        $this->expirationDelay = $delay;
    }

    protected function initialize(SaleInterface $sale): void
    {
        parent::initialize($sale);

        if (!$sale instanceof QuoteInterface) {
            throw new UnexpectedTypeException($sale, QuoteInterface::class);
        }

        $this->inChargeResolver->update($sale);

        $date = new DateTime();
        $date->modify($this->expirationDelay)->setTime(0, 0);
        $sale->setExpiresAt($date);
    }
}
