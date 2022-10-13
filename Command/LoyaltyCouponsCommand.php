<?php

declare(strict_types=1);

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
    protected static $defaultName        = 'ekyna:commerce:loyalty:coupons';
    protected static $defaultDescription = 'Generates coupon rewards for customer loyalty.';

    public function __construct(
        private readonly CouponGenerator $generator,
        private readonly Mailer          $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->generator->generate();

        if (empty($result)) {
            return Command::SUCCESS;
        }

        foreach ($result as $data) {
            $this->mailer->sendCustomerCoupons($data['customer'], $data['coupons']);
        }

        return Command::SUCCESS;
    }
}
