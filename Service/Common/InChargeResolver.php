<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class InChargeResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InChargeResolver
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var EntityManagerInterface
     */
    private $userManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


    /**
     * Constructor.
     *
     * @param UserProviderInterface         $userProvider
     * @param EntityManagerInterface        $userManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        UserProviderInterface $userProvider,
        EntityManagerInterface $userManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->userProvider = $userProvider;
        $this->userManager = $userManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Updates the subject's 'in charge' user.
     *
     * @param InChargeSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(InChargeSubjectInterface $subject)
    {
        if (null !== $subject->getInCharge()) {
            return false;
        }

        if (null !== $inCharge = $this->resolve($subject)) {
            $inCharge = $this->userManager->merge($inCharge);

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
     * @return \Ekyna\Bundle\UserBundle\Model\UserInterface|null
     */
    public function resolve(InChargeSubjectInterface $subject)
    {
        if ($subject instanceof SaleInterface) {
            if (null !== $inCharge = $this->resolveSale($subject)) {
                return $inCharge;
            }
        }

        if (null !== $user = $this->userProvider->getUser()) {
            if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Resolves the given sale's 'in charge' user.
     *
     * @param SaleInterface $sale
     *
     * @return \Ekyna\Bundle\UserBundle\Model\UserInterface|null
     */
    private function resolveSale(SaleInterface $sale)
    {
        /** @var CustomerInterface $customer */
        if (null !== $customer = $sale->getCustomer()) {
            if (null !== $inCharge = $customer->getInCharge()) {
                return $inCharge;
            }

            if (null !== $customer = $customer->getParent()) {
                if (null !== $inCharge = $customer->getInCharge()) {
                    return $inCharge;
                }
            }
        }

        return null;
    }
}
