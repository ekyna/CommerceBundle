<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Component\Commerce\Customer\Loyalty\CouponGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LoyaltyCouponsCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyCouponsCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:loyalty:coupons';

    /**
     * @var CouponGenerator
     */
    private $generator;

    /**
     * @var Mailer
     */
    private $mailer;


    /**
     * Constructor.
     *
     * @param CouponGenerator $generator
     * @param Mailer          $mailer
     */
    public function __construct(CouponGenerator $generator, Mailer $mailer)
    {
        parent::__construct();

        $this->generator = $generator;
        $this->mailer = $mailer;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Generates coupon rewards for customer loyalty.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->generator->generate();

        if (empty($result)) {
            return;
        }

        foreach ($result as $data) {
            $this->mailer->sendCustomerCoupons($data['customer'], $data['coupons']);
        }
    }
}
