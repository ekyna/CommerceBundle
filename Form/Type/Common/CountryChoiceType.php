<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Intl;
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
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var string
     */
    private $countryClass;

    /**
     * @var string
     */
    private $defaultCode;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     * @param string                  $countryClass
     * @param string                  $defaultCode
     */
    public function __construct(LocaleProviderInterface $localeProvider, $countryClass, $defaultCode)
    {
        $this->localeProvider = $localeProvider;
        $this->countryClass = $countryClass;
        $this->defaultCode = $defaultCode;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $queryBuilderDefault = function (Options $options, $value) {
            if (is_callable($value)) {
                return $value;
            }

            if ($options['enabled']) {
                return function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('o');

                    return $qb->andWhere($qb->expr()->eq('o.enabled', true));
                };
            }

            return null;
        };

        $currentLocale = $this->localeProvider->getCurrentLocale();

        $resolver
            ->setDefaults([
                'label'             => function (Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    return 'ekyna_commerce.country.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'class'             => $this->countryClass,
                'enabled'           => true,
                'query_builder'     => $queryBuilderDefault,
                'preferred_choices' => function (CountryInterface $country) {
                    return $country->getCode() === $this->defaultCode;
                },
                'choice_label'      => function (CountryInterface $country) use ($currentLocale) {
                    return Intl::getRegionBundle()->getCountryName($country->getCode(), $currentLocale);
                },
            ])
            ->setAllowedTypes('enabled', 'bool')
            ->setNormalizer('attr', function (Options $options, $value) {
                $value = (array)$value;

                if (!isset($value['placeholder'])) {
                    $value['placeholder'] = 'ekyna_commerce.country.label.' . ($options['multiple'] ? 'plural' : 'singular');
                }

                return $value;
            });
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
