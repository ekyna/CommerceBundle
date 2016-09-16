<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractItemController extends ResourceController
{
    /**
     * (Re)configures the item.
     *
     * @param Request $request
     */
    public function configureAction(Request $request)
    {

    }
}
