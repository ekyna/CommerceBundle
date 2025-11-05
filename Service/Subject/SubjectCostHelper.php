<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Class SubjectCostHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubjectCostHelper
{
    public function __construct(
        private readonly SubjectCostGuesserInterface $costGuesser,
    ) {
    }

    public function guess(SubjectInterface $subject): Cost
    {
        return $this->costGuesser->guess($subject) ?? new Cost();
    }
}
