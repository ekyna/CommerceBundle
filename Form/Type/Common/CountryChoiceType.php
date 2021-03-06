<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\CountryChoiceLoader;
use Ekyna\Bundle\CoreBundle\Form\DataTransformer\ObjectToIdentifierTransformer;
use Ekyna\Component\Commerce\Common\Country\CountryProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CountryChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryChoiceType extends AbstractType
{
    /**
     * @var CountryProviderInterface
     */
    private $countryProvider;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param CountryProviderInterface $countryProvider
     * @param LocaleProviderInterface  $localeProvider
     */
    public function __construct(CountryProviderInterface $countryProvider, LocaleProviderInterface $localeProvider)
    {
        $this->countryProvider = $countryProvider;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @noinspection PhpParamsInspection */
        $builder->addModelTransformer(new ObjectToIdentifierTransformer(
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

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $userCountry = $this->countryProvider->getCountry();

        $resolver
            ->setDefaults([
                'label'                     => function (Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    return 'ekyna_commerce.country.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'enabled'                   => true,
                'choice_loader'             => function (Options $options) {
                    if ($options['choices']) {
                        @trigger_error(sprintf(
                            'Using the "choices" option in %s has been deprecated since Symfony 3.3 and will be ' .
                            'ignored in 4.0. Override the "choice_loader" option instead or set it to null.',
                            __CLASS__
                        ), E_USER_DEPRECATED);

                        return null;
                    }

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
                    $value['placeholder'] = 'ekyna_commerce.country.label.' .
                        ($options['multiple'] ? 'plural' : 'singular');
                }

                return $value;
            });
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
