<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Country;

use Ekyna\Bundle\CoreBundle\Service\Geo\UserCountryGuesser;
use Ekyna\Component\Commerce\Bridge\Symfony\Country\SessionCountryProvider as BaseProvider;

/**
 * Class SessionCountryProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Country
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SessionCountryProvider extends BaseProvider
{
    /**
     * @var UserCountryGuesser
     */
    private $countryGuesser;


    /**
     * Sets the country guesser.
     *
     * @param UserCountryGuesser $guesser
     */
    public function setCountryGuesser(UserCountryGuesser $guesser)
    {
        $this->countryGuesser = $guesser;
    }

    /**
     * @inheritDoc
     */
    protected function guessCountry(): ?string
    {
        return $this->countryGuesser->getUserCountry();
    }
}
