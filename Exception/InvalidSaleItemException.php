<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Exception;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Exception;

/**
 * Class InvalidSaleItemException
 * @package Ekyna\Bundle\CommerceBundle\Exception
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvalidSaleItemException extends Exception implements CommerceExceptionInterface
{

}
