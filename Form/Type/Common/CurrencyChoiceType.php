<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\CurrencyChoiceLoader;
use Ekyna\Bundle\CoreBundle\Form\DataTransformer\ObjectToIdentifierTransformer;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurrencyChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyChoiceType extends AbstractType
{
    /**
     * @var CurrencyProviderInterface
     */
    private $currencyProvider;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param CurrencyProviderInterface $currencyProvider
     * @param LocaleProviderInterface   $localeProvider
     */
    public function __construct(CurrencyProviderInterface $currencyProvider, LocaleProviderInterface $localeProvider)
    {
        $this->currencyProvider = $currencyProvider;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @noinspection PhpParamsInspection */
        $builder->addModelTransformer(new ObjectToIdentifierTransformer(
            $this->currencyProvider->getCurrencyRepository(),
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
        $userCurrency = $this->currencyProvider->getCurrency();

        $resolver
            ->setDefaults([
                'label'                     => function (Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    return 'ekyna_commerce.currency.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'enabled'                   => true,
                'choice_loader'             => function (Options $options) {
                    return new CurrencyChoiceLoader(
                        $this->currencyProvider->getCurrencyRepository(),
                        $this->localeProvider->getCurrentLocale(),
                        $options['enabled']
                    );
                },
                'choice_translation_domain' => false,
                'preferred_choices'         => function (string $code) use ($userCurrency) {
                    return strtoupper($code) === strtoupper($userCurrency->getCode());
                },
            ])
            ->setAllowedTypes('enabled', 'bool')
            ->setNormalizer('attr', function (Options $options, $value) {
                $value = (array)$value;

                if (!isset($value['placeholder'])) {
                    $value['placeholder'] = 'ekyna_commerce.currency.label.' .
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
