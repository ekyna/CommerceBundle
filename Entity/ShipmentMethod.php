<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentMethodInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Component\Commerce\Shipment\Entity\ShipmentMethod as BaseMethod;

/**
 * Class ShipmentMethod
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethod extends BaseMethod implements ShipmentMethodInterface
{
    use MediaSubjectTrait;
}
