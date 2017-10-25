<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Quote\Entity\Quote as BaseQuote;

/**
 * Class Quote
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Quote extends BaseQuote implements QuoteInterface
{
    /**
     * @var UserInterface
     */
    protected $inCharge;


    /**
     * @inheritdoc
     */
    public function getInCharge()
    {
        return $this->inCharge;
    }

    /**
     * @inheritdoc
     */
    public function setInCharge(UserInterface $user = null)
    {
        $this->inCharge = $user;

        return $this;
    }
}
