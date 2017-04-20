<?php

declare(strict_types=1);

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
    public static function getConfig(): array
    {
        $prefix = 'supplier_order_attachment.type.';

        return [
            Types::TYPE_FORM      => [$prefix . Types::TYPE_FORM],
            Types::TYPE_PROFORMA  => [$prefix . Types::TYPE_PROFORMA],
            Types::TYPE_PAYMENT   => [$prefix . Types::TYPE_PAYMENT],
            Types::TYPE_FORWARDER => [$prefix . Types::TYPE_FORWARDER],
            Types::TYPE_IMPORT    => [$prefix . Types::TYPE_IMPORT],
            Types::TYPE_DELIVERY  => [$prefix . Types::TYPE_DELIVERY],
        ];
    }

    public static function getTheme(string $constant): ?string
    {
        return null;
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
