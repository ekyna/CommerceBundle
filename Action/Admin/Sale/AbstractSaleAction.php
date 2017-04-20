<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class AbstractSaleAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleAction extends AbstractAction implements AdminActionInterface
{
    protected function getSale(): ?SaleInterface
    {
        if (null === $sale = $this->context->getResource()) {
            return null;
        }

        if ($sale instanceof SaleInterface) {
            return $sale;
        }

        return null;
    }
}
