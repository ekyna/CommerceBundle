<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\User\Service\UserProviderInterface;

/**
 * Class InChargeResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InChargeResolver
{
    private UserProviderInterface $userProvider;


    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Updates the subject's 'in charge' user.
     *
     * @param InChargeSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(InChargeSubjectInterface $subject): bool
    {
        if (null !== $subject->getInCharge()) {
            return false;
        }

        if (null !== $inCharge = $this->resolve($subject)) {
            $subject->setInCharge($inCharge);

            return true;
        }

        return false;
    }

    /**
     * Resolves the given subject's 'in charge' user.
     *
     * @param InChargeSubjectInterface $subject
     *
     * @return UserInterface|null
     */
    public function resolve(InChargeSubjectInterface $subject): ?UserInterface
    {
        $this->userProvider->reset();

        /** @var UserInterface $user */
        if (null !== $user = $this->userProvider->getUser()) {
            return $user;
        }

        if ($subject instanceof SaleInterface) {
            if (null !== $inCharge = $this->resolveSale($subject)) {
                return $inCharge;
            }
        }

        return null;
    }

    /**
     * Resolves the given sale's 'in charge' user.
     *
     * @param SaleInterface $sale
     *
     * @return UserInterface|null
     */
    private function resolveSale(SaleInterface $sale): ?UserInterface
    {
        /** @var CustomerInterface $customer */
        if (null !== $customer = $sale->getCustomer()) {
            if (null !== $inCharge = $customer->getInCharge()) {
                return $inCharge;
            }

            /** @var CustomerInterface $customer */
            if (null !== $customer = $customer->getParent()) {
                if (null !== $inCharge = $customer->getInCharge()) {
                    return $inCharge;
                }
            }
        }

        return null;
    }
}
