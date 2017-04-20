<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Common\Model\Genders as Constants;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class Genders
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
final class Genders extends AbstractConstants
{
    public static function getConfig(): array
    {
        $short = 'gender.short.';
        $long  = 'gender.long.';

        return [
            Constants::GENDER_MR   => [$short.Constants::GENDER_MR,   $long.Constants::GENDER_MR],
            Constants::GENDER_MRS  => [$short.Constants::GENDER_MRS,  $long.Constants::GENDER_MRS],
            Constants::GENDER_MISS => [$short.Constants::GENDER_MISS, $long.Constants::GENDER_MISS],
        ];
    }

    public static function getLabel(string $constant, bool $long = false): TranslatableInterface
    {
        Genders::isValid($constant, true);

        return t(Genders::getConfig()[$constant][$long ? 1 : 0], [], 'EkynaCommerce');
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
