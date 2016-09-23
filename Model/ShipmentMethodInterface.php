<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface as BaseInterface;

/**
 * Interface ShipmentMethodInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentMethodInterface extends BaseInterface, MediaSubjectInterface
{

}
