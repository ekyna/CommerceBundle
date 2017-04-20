<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;

/**
 * Class AbstractOrderAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractOrderAction extends AbstractAction implements AdminActionInterface
{
    protected function getOrder(): ?OrderInterface
    {
        if (null === $order = $this->context->getResource()) {
            return null;
        }

        if ($order instanceof OrderInterface) {
            return $order;
        }

        return null;
    }
}
