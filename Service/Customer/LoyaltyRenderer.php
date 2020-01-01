<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\LoyaltyLogRepositoryInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LoyaltyRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyRenderer
{
    /**
     * @var LoyaltyLogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var EngineInterface
     */
    private $engine;


    /**
     * Constructor.
     *
     * @param LoyaltyLogRepositoryInterface $logRepository
     * @param CouponRepositoryInterface     $couponRepository
     * @param EngineInterface               $engine
     */
    public function __construct(
        LoyaltyLogRepositoryInterface $logRepository,
        CouponRepositoryInterface $couponRepository,
        EngineInterface $engine
    ) {
        $this->logRepository = $logRepository;
        $this->couponRepository = $couponRepository;
        $this->engine = $engine;
    }

    /**
     * Renders the customer loyalty logs.
     *
     * @param CustomerInterface $customer
     *
     * @return string
     */
    public function renderLogs(CustomerInterface $customer): string
    {
        return $this->engine->render('@EkynaCommerce/Customer/loyalty_logs.html.twig', [
            'logs' => $this->logRepository->findByCustomer($customer),
        ]);
    }

    /**
     * Renders the customer coupons.
     *
     * @param CustomerInterface $customer
     *
     * @return string
     */
    public function renderCoupons(CustomerInterface $customer): string
    {
        return $this->engine->render('@EkynaCommerce/Customer/coupons.html.twig', [
            'coupons' => $this->couponRepository->findByCustomer($customer),
        ]);
    }
}
