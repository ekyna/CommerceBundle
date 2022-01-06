<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Extension;

use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhoneNumberTypeExtension
 * @package Ekyna\Bundle\CommerceBundle\Form\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PhoneNumberTypeExtension extends AbstractTypeExtension
{
    private CountryProviderInterface $countryProvider;

    /**
     * Constructor.
     *
     * @param CountryProviderInterface $countryProvider
     */
    public function __construct(CountryProviderInterface $countryProvider)
    {
        $this->countryProvider = $countryProvider;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('default_country', $this->countryProvider->getCurrentCountry());
    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): iterable
    {
        return [PhoneNumberType::class];
    }
}
