<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

/**
 * Class AddressImportLabel
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AddressImportLabel
{
    /**
     * @var array
     */
    private static $columnKeys = [
        'company'    => 'ekyna_core.field.company',
        'gender'     => 'ekyna_core.field.gender',
        'firstName'  => 'ekyna_core.field.first_name',
        'lastName'   => 'ekyna_core.field.last_name',
        'street'     => 'ekyna_core.field.street',
        'complement' => 'ekyna_commerce.address.field.complement',
        'supplement' => 'ekyna_commerce.address.field.supplement',
        'extra'      => 'ekyna_commerce.address.field.extra',
        'postalCode' => 'ekyna_core.field.postal_code',
        'city'       => 'ekyna_core.field.city',
        'country'    => 'ekyna_commerce.country.label.singular',
        // TODO 'state',
        'phone'      => 'ekyna_core.field.phone',
        'mobile'     => 'ekyna_core.field.mobile',
        'digicode1'  => 'ekyna_commerce.address.field.digicode1',
        'digicode2'  => 'ekyna_commerce.address.field.digicode2',
        'intercom'   => 'ekyna_commerce.address.field.intercom',
    ];

    /**
     * Returns the column label.
     *
     * @param string $key
     *
     * @return string
     */
    public static function getLabel(string $key): string
    {
        if (isset(self::$columnKeys[$key])) {
            return self::$columnKeys[$key];
        }

        return null;
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
