<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\LoyaltyLogRepositoryInterface;
use Twig\Environment;

/**
 * Class LoyaltyRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyRenderer
{
    private LoyaltyLogRepositoryInterface $logRepository;
    private CouponRepositoryInterface $couponRepository;
    private Environment $engine;


    public function __construct(
        LoyaltyLogRepositoryInterface $logRepository,
        CouponRepositoryInterface $couponRepository,
        Environment $engine
    ) {
        $this->logRepository = $logRepository;
        $this->couponRepository = $couponRepository;
        $this->engine = $engine;
    }

    /**
     * Renders the customer loyalty logs.
     */
    public function renderLogs(CustomerInterface $customer): string
    {
        return $this->engine->render('@EkynaCommerce/Customer/loyalty_logs.html.twig', [
            'logs' => $this->logRepository->findByCustomer($customer),
        ]);
    }

    /**
     * Renders the customer coupons.
     */
    public function renderCoupons(CustomerInterface $customer): string
    {
        return $this->engine->render('@EkynaCommerce/Customer/coupons.html.twig', [
            'coupons' => $this->couponRepository->findByCustomer($customer),
        ]);
    }
}
