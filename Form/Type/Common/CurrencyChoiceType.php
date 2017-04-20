<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\CurrencyChoiceLoader;
use Ekyna\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeDoctrineCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function strtoupper;
use function Symfony\Component\Translation\t;

/**
 * Class CurrencyChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyChoiceType extends AbstractType
{
    private CurrencyProviderInterface $currencyProvider;
    private LocaleProviderInterface   $localeProvider;


    public function __construct(CurrencyProviderInterface $currencyProvider, LocaleProviderInterface $localeProvider)
    {
        $this->currencyProvider = $currencyProvider;
        $this->localeProvider = $localeProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ResourceToIdentifierTransformer(
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $userCurrency = $this->currencyProvider->getCurrency();

        $resolver
            ->setDefaults([
                'label'                     => function (Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    $id = 'currency.label.' . ($options['multiple'] ? 'plural' : 'singular');
                    return t($id, [], 'EkynaCommerce');
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
                'preferred_choices'         => function (string $code) use ($userCurrency): bool {
                    return strtoupper($code) === strtoupper($userCurrency->getCode());
                },
            ])
            ->setAllowedTypes('enabled', 'bool')
            ->setNormalizer('attr', function (Options $options, $value) {
                $value = (array)$value;

                if (!isset($value['placeholder'])) {
                    $id = 'currency.label.' . ($options['multiple'] ? 'plural' : 'singular');
                    $value['placeholder'] = t($id, [], 'EkynaCommerce');
                }

                return $value;
            });
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
