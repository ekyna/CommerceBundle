<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Commerce\Quote\Entity\Quote as BaseQuote;

/**
 * Class Quote
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Model\CustomerInterface $customer
 */
class Quote extends BaseQuote implements Model\QuoteInterface
{
    use Model\InChargeSubjectTrait;
    use Model\TaggedSaleTrait;


    public function __construct()
    {
        parent::__construct();

        $this->initializeTags();
    }
}
