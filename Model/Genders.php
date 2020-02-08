<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Common\Model\Genders as Constants;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class Genders
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class Genders extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig(): array
    {
        $short = 'ekyna_commerce.gender.short.';
        $long  = 'ekyna_commerce.gender.long.';

        return [
            Constants::GENDER_MR   => [$short.Constants::GENDER_MR,   $long.Constants::GENDER_MR],
            Constants::GENDER_MRS  => [$short.Constants::GENDER_MRS,  $long.Constants::GENDER_MRS],
            Constants::GENDER_MISS => [$short.Constants::GENDER_MISS, $long.Constants::GENDER_MISS],
        ];
    }

    /**
     * Returns the label for the given constant.
     *
     * @param mixed $constant
     * @param bool  $long
     *
     * @return string
     */
    public static function getLabel(string $constant, bool $long = false): string
    {
        static::isValid($constant, true);

        return static::getConfig()[$constant][$long ? 1 : 0];
    }

    /**
     * @inheritDoc
     */
    public static function getTheme(string $constant): ?string
    {
        return null;
    }
}
