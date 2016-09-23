<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller;

/**
 * Class ShipmentMethodController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodController extends Controller\ResourceController
{
    use Controller\Resource\ToggleableTrait,
        Controller\Resource\SortableTrait;
}
