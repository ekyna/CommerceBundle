<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\ChoiceList;

use Collator;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Intl\Currencies;

use function array_filter;
use function array_flip;
use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * Class CurrencyChoiceLoader
 * @package Ekyna\Bundle\CommerceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyChoiceLoader implements ChoiceLoaderInterface
{
    private CurrencyRepositoryInterface $repository;
    private bool                        $enabled;
    private string                      $locale;

    private ?ArrayChoiceList $choiceLists = null;


    /**
     * Constructor.
     *
     * @param CurrencyRepositoryInterface $repository
     * @param string                      $locale
     * @param bool                        $enabled
     */
    public function __construct(CurrencyRepositoryInterface $repository, string $locale, bool $enabled)
    {
        $this->repository = $repository;
        $this->locale = $locale;
        $this->enabled = $enabled;
    }

    /**
     * @inheritDoc
     */
    public function loadChoicesForValues(array $values, callable $value = null): array
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
    public function loadChoiceList(callable $value = null): ChoiceListInterface
    {
        if (null !== $this->choiceLists) {
            return $this->choiceLists;
        }

        $codes = $this->enabled
            ? $this->repository->findEnabledCodes()
            : $this->repository->findAllCodes();

        $currencies = [];
        foreach ($codes as $code) {
            $currencies[$code] = mb_convert_case(Currencies::getName($code, $this->locale), MB_CASE_TITLE);
        }

        $collator = new Collator($this->locale);
        $collator->asort($currencies);

        return $this->choiceLists = new ArrayChoiceList(array_flip($currencies), $value);
    }

    /**
     * @inheritDoc
     */
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
