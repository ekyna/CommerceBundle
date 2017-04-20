<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\DecimalToStringTransformer;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Locale;
use NumberFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function preg_match;

/**
 * Class MoneyType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MoneyType extends AbstractType
{
    protected static array $configs = [];

    private CurrencyConverterInterface $currencyConverter;
    private string $defaultCurrency;

    public function __construct(CurrencyConverterInterface $currencyConverter, string $defaultCurrency)
    {
        $this->currencyConverter = $currencyConverter;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['decimal']) {
            $builder->addModelTransformer(new DecimalToStringTransformer($options['scale']));
        }

        $builder
            ->addViewTransformer(new MoneyToLocalizedStringTransformer(
                $options['scale'],
                $options['grouping'],
                null,
                $options['divisor']
            ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $rate = $this->getRate($options);

        $view->vars['base_config'] = self::getConfig($options['base']);

        if ($options['base'] === $options['quote']) {
            return;
        }

        FormUtil::addClass($view, 'commerce-money-base');

        $view->vars['quote_config'] = self::getConfig($options['quote']);

        $view->vars['title'] = "{$options['base']}/{$options['quote']}: $rate";

        $view->vars['config'] = [
            'rate'  => $rate,
            'scale' => $options['scale'],
        ];
    }

    /**
     * Returns the conversion rate.
     */
    private function getRate(array $options): Decimal
    {
        /** @var ExchangeSubjectInterface $subject */
        $subject = $options['subject'];
        if ($subject) {
            return $this
                ->currencyConverter
                ->getSubjectExchangeRate($subject, $options['base'], $options['quote']);
        }

        if ($quote = $options['quote']) {
            return $this
                ->currencyConverter
                ->getRate($options['base'], $quote);
        }

        throw new RuntimeException("You must define 'subject' or 'quote' option.");
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'decimal' => true,
                'base'     => $this->defaultCurrency,
                'quote'    => null,
                'subject'  => null,
                'scale'    => 2,
                'grouping' => false,
                'divisor'  => 1,
                'compound' => false,
            ])
            ->setAllowedTypes('decimal', 'bool')
            ->setAllowedTypes('scale', 'int')
            ->setAllowedTypes('base', [CurrencyInterface::class, 'string'])
            ->setAllowedTypes('quote', [CurrencyInterface::class, 'string', 'null'])
            ->setAllowedTypes('subject', [ExchangeSubjectInterface::class, 'null'])
            ->setNormalizer('base', function (Options $options, $value) {
                if ($value instanceof CurrencyInterface) {
                    return $value->getCode();
                }

                return $value;
            })
            ->setNormalizer('quote', function (Options $options, $value) {
                /** @var ExchangeSubjectInterface $subject */
                $subject = $options['subject'];
                if ($subject && ($currency = $subject->getCurrency())) {
                    return $currency->getCode();
                }

                if (is_null($value)) {
                    $value = $options['base'];
                }

                if ($value instanceof CurrencyInterface) {
                    return $value->getCode();
                }

                return $value;
            })
            ->setNormalizer('empty_data', function (Options $options, $value) {
                if (!is_callable($value) && !is_null($value)) {
                    return $value;
                }

                if ($options['required']) {
                    return new Decimal(0);
                }

                return $value;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_money';
    }

    /**
     * Returns the config for this currency and locale
     */
    protected static function getConfig(string $currency = null): array
    {
        if (!$currency) {
            return [];
        }

        $locale = Locale::getDefault();

        if (!isset(self::$configs[$locale])) {
            self::$configs[$locale] = [];
        }

        if (isset(self::$configs[$locale][$currency])) {
            return self::$configs[$locale][$currency];
        }

        $format = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $pattern = $format->formatCurrency(123.0, $currency);

        // the spacings between currency symbol and number are ignored, because
        // a single space leads to better readability in combination with input
        // fields
        // the regex also considers non-break spaces (0xC2 or 0xA0 in UTF-8)

        preg_match(
            '/^([^\s\xc2\xa0]*)[\s\xc2\xa0]*123(?:[,.]0+)?[\s\xc2\xa0]*([^\s\xc2\xa0]*)$/u',
            $pattern,
            $matches
        );

        if (!empty($matches[1])) {
            return self::$configs[$locale][$currency] = [
                'left'   => true,
                'symbol' => $matches[1],
            ];
        }

        if (!empty($matches[2])) {
            return self::$configs[$locale][$currency] = [
                'left'   => false,
                'symbol' => $matches[2],
            ];
        }

        return self::$configs[$locale][$currency] = [
            'left'   => true,
            'symbol' => $matches[2],
        ];
    }
}
