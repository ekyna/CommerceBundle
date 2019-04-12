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
     * @var string
     */
    private $defaultCountry;


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
     * Sets the default country code.
     *
     * @param string $code
     */
    public function setDefaultCountry(string $code)
    {
        $this->defaultCountry = $code;
    }

    /**
     * @inheritDoc
     */
    protected function guessCountry(): ?string
    {
        return $this->countryGuesser->getUserCountry($this->defaultCountry);
    }
}
