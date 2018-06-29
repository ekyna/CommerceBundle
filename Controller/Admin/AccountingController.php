<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Resource\SortableTrait;
use Ekyna\Bundle\AdminBundle\Controller\Resource\ToggleableTrait;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;

/**
 * Class AccountingController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingController extends ResourceController
{
    use SortableTrait,
        ToggleableTrait;
}
