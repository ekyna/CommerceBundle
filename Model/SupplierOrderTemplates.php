<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates as States;

/**
 * Class SupplierOrderTemplates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderTemplates extends AbstractConstants
{
    const ESTIMATED_DATE_OF_ARRIVAL = 'estimated_date_of_arrival';
    const TRACKING_INFORMATION      = 'tracking_information';

    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.supplier_order.template.';
        $suffix = '.label';

        return [
            static::ESTIMATED_DATE_OF_ARRIVAL => [$prefix . static::ESTIMATED_DATE_OF_ARRIVAL . $suffix],
            static::TRACKING_INFORMATION      => [$prefix . static::TRACKING_INFORMATION . $suffix],
        ];
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
