<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

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
     * Constructor.
     *
     * @param CouponGenerator $generator
     */
    public function __construct(CouponGenerator $generator)
    {
        parent::__construct();

        $this->generator = $generator;
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
        $this->generator->generate();
    }
}
