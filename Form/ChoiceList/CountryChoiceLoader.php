<?php

namespace Ekyna\Bundle\CommerceBundle\Form\ChoiceList;

use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Intl\Intl;

/**
 * Class CountryChoiceLoader
 * @package Ekyna\Bundle\CommerceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var CountryRepositoryInterface
     */
    private $repository;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var ArrayChoiceList
     */
    private $choiceList;


    /**
     * Constructor.
     *
     * @param CountryRepositoryInterface $repository
     * @param string                     $locale
     * @param bool                       $enabled
     */
    public function __construct(CountryRepositoryInterface $repository, string $locale, bool $enabled)
    {
        $this->repository = $repository;
        $this->locale = $locale;
        $this->enabled = $enabled;
    }

    /**
     * @inheritDoc
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * @inheritDoc
     */
    public function loadChoiceList($value = null)
    {
        if (null !== $this->choiceList) {
            return $this->choiceList;
        }

        $bundle = Intl::getRegionBundle();

        $codes = $this->enabled
            ? $this->repository->findEnabledCodes()
            : $this->repository->findAllCodes();

        $countries = [];
        foreach ($codes as $code) {
            $countries[$code] = mb_convert_case($bundle->getCountryName($code, $this->locale), MB_CASE_TITLE);
        }

        $collator = new \Collator($this->locale);
        $collator->asort($countries);

        return $this->choiceList = new ArrayChoiceList(array_flip($countries), $value);
    }

    /**
     * @inheritDoc
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return [];
        }

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
