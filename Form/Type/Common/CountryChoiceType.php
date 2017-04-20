<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\CountryChoiceLoader;
use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CountryChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryChoiceType extends AbstractType
{
    private CountryProviderInterface $countryProvider;
    private LocaleProviderInterface  $localeProvider;

    public function __construct(CountryProviderInterface $countryProvider, LocaleProviderInterface $localeProvider)
    {
        $this->countryProvider = $countryProvider;
        $this->localeProvider = $localeProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ResourceToIdentifierTransformer(
            $this->countryProvider->getCountryRepository(),
            'code',
            $options['multiple']
        ));

        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeDoctrineCollectionListener())
                ->addViewTransformer(new CollectionToArrayTransformer(), true);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $userCountry = $this->countryProvider->getCountry();

        $resolver
            ->setDefaults([
                'label'                     => function (Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    return t('country.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
                },
                'enabled'                   => true,
                'choice_loader'             => function (Options $options) {
                    return new CountryChoiceLoader(
                        $this->countryProvider->getCountryRepository(),
                        $this->localeProvider->getCurrentLocale(),
                        $options['enabled']
                    );
                },
                'choice_translation_domain' => false,
                'preferred_choices'         => function (string $code) use ($userCountry) {
                    return strtoupper($code) === strtoupper($userCountry->getCode());
                },
            ])
            ->setAllowedTypes('enabled', 'bool')
            ->setNormalizer('attr', function (Options $options, $value) {
                $value = (array)$value;

                if (!isset($value['placeholder'])) {
                    $value['placeholder'] =
                        t('country.label.' . ($options['multiple'] ? 'plural' : 'singular'), [], 'EkynaCommerce');
                }

                return $value;
            });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
