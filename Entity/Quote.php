<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Commerce\Quote\Entity\Quote as BaseQuote;

/**
 * Class Quote
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Quote extends BaseQuote implements Model\QuoteInterface
{
    use Model\InChargeSubjectTrait;
}
