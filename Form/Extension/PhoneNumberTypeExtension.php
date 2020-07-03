<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Extension;

use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
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
    /**
     * @var CountryProviderInterface
     */
    private $countryProvider;

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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('default_country', $this->countryProvider->getCurrentCountry());
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return PhoneNumberType::class;
    }
}
