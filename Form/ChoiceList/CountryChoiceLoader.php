<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\ChoiceList;

use Collator;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Intl\Countries;

use function array_filter;
use function array_flip;
use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * Class CountryChoiceLoader
 * @package Ekyna\Bundle\CommerceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountryChoiceLoader implements ChoiceLoaderInterface
{
    private CountryRepositoryInterface $repository;
    private bool                       $enabled;
    private string                     $locale;

    private ?ArrayChoiceList $choiceList = null;

    public function __construct(CountryRepositoryInterface $repository, string $locale, bool $enabled)
    {
        $this->repository = $repository;
        $this->locale = $locale;
        $this->enabled = $enabled;
    }

    public function loadChoicesForValues(array $values, callable $value = null): array
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    public function loadChoiceList(callable $value = null): ChoiceListInterface
    {
        if (null !== $this->choiceList) {
            return $this->choiceList;
        }

        $codes = $this->repository->getNames($this->enabled);

        $names = Countries::getNames($this->locale);

        $countries = [];
        foreach ($codes as $code => $name) {
            $countries[$code] = isset($names[$code])
                ? mb_convert_case($names[$code], MB_CASE_TITLE)
                : $name;
        }

        $collator = new Collator($this->locale);
        $collator->asort($countries);

        return $this->choiceList = new ArrayChoiceList(array_flip($countries), $value);
    }

    public function loadValuesForChoices(array $choices, callable $value = null): array
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
