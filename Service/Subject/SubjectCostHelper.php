<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Subject\Calculator\SubjectCostCalculatorInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectCostHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectCostHelper
{
    public function __construct(
        private readonly SubjectCostCalculatorInterface $costCalculator,
    ) {
    }

    public function calculate(SubjectInterface $subject): Cost
    {
        return $this->costCalculator->calculate($subject) ?? new Cost();
    }
}
