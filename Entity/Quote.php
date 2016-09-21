<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Entity\Quote as BaseQuote;

/**
 * Class Quote
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Quote extends BaseQuote implements QuoteInterface
{
    /**
     * @var string
     */
    private $gender;


    /**
     * @inheritdoc
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @inheritdoc
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }
}
