<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AddressImportLabel
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AddressImportLabel
{
    private static array $columnKeys = [
        'company'    => ['field.company', 'EkynaUi'],
        'gender'     => ['field.gender', 'EkynaUi'],
        'firstName'  => ['field.first_name', 'EkynaUi'],
        'lastName'   => ['field.last_name', 'EkynaUi'],
        'street'     => ['field.street', 'EkynaUi'],
        'complement' => ['address.field.complement', 'EkynaCommerce'],
        'supplement' => ['address.field.supplement', 'EkynaCommerce'],
        'extra'      => ['address.field.extra', 'EkynaCommerce'],
        'postalCode' => ['field.postal_code', 'EkynaUi'],
        'city'       => ['field.city', 'EkynaUi'],
        'country'    => ['country.label.singular', 'EkynaCommerce'],
        // TODO 'state',
        'phone'      => ['field.phone', 'EkynaUi'],
        'mobile'     => ['field.mobile', 'EkynaUi'],
        'digicode1'  => ['address.field.digicode1', 'EkynaCommerce'],
        'digicode2'  => ['address.field.digicode2', 'EkynaCommerce'],
        'intercom'   => ['address.field.intercom', 'EkynaCommerce'],
    ];

    /**
     * Returns the column label.
     */
    public static function getLabel(string $key): TranslatableInterface
    {
        if (isset(self::$columnKeys[$key])) {
            return t(self::$columnKeys[$key][0], [], self::$columnKeys[$key][1]);
        }

        throw new InvalidArgumentException("Unknown address property '$key'.");
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
