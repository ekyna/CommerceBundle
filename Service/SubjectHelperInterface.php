<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\HelperInterface;

/**
 * Interface SubjectHelperInterface
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectHelperInterface extends HelperInterface
{
    /**
     * Returns the sale item form options.
     *
     * @param SaleItemInterface $item
     * @param string             $property
     * @return array
     * @throws InvalidArgumentException
     */
    public function getFormOptions(SaleItemInterface $item, $property);
}
