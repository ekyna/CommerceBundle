<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;

/**
 * Class QuoteVoucher
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteVoucher
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var SaleAttachmentInterface
     */
    private $attachment;

    /**
     * Returns the number.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Sets the number.
     *
     * @param string $number
     *
     * @return QuoteVoucher
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Returns the attachment.
     *
     * @return SaleAttachmentInterface
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Sets the attachment.
     *
     * @param SaleAttachmentInterface $attachment
     *
     * @return QuoteVoucher
     */
    public function setAttachment(SaleAttachmentInterface $attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }
}
