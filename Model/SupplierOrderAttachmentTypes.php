<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderAttachmentTypes as Types;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class SupplierOrderAttachmentTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderAttachmentTypes extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.supplier_order_attachment.type.';

        return [
            Types::TYPE_PROFORMA  => [$prefix . Types::TYPE_PROFORMA],
            Types::TYPE_PAYMENT   => [$prefix . Types::TYPE_PAYMENT],
            Types::TYPE_FORWARDER => [$prefix . Types::TYPE_FORWARDER],
            Types::TYPE_IMPORT    => [$prefix . Types::TYPE_IMPORT],
            Types::TYPE_DELIVERY  => [$prefix . Types::TYPE_DELIVERY],
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