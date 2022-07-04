<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Country;

use Ekyna\Bundle\UiBundle\Service\Geo\UserCountryGuesser;
use Ekyna\Component\Commerce\Bridge\Symfony\Country\SessionCountryProvider as BaseProvider;

/**
 * Class SessionCountryProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCountryProvider extends BaseProvider
{
    private UserCountryGuesser $countryGuesser;

    public function setCountryGuesser(UserCountryGuesser $guesser): void
    {
        $this->countryGuesser = $guesser;
    }

    protected function guessCountry(): ?string
    {
        return $this->countryGuesser->getUserCountry($this->getFallbackCountry());
    }
}
