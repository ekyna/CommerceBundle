<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaxGroupChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupChoiceType extends AbstractType
{
    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var string
     */
    private $taxGroupClass;


    /**
     * Constructor.
     *
     * @param CountryRepositoryInterface $countryRepository
     * @param string                     $taxGroupClass
     */
    public function __construct(CountryRepositoryInterface $countryRepository, $taxGroupClass)
    {
        $this->countryRepository = $countryRepository;
        $this->taxGroupClass = $taxGroupClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaultCountry = $this->countryRepository->findDefault();

        /**
         * Choice attributes builder.
         *
         * @param TaxGroupInterface $group
         *
         * @return array
         */
        $choiceAttr = function ($group) use ($defaultCountry) {
            $taxes = $group->getTaxes()->filter(function (TaxInterface $tax) use ($defaultCountry) {
                return $tax->getCountry() === $defaultCountry;
            })->toArray();

            $taxes = array_map(function (TaxInterface $tax) {
                return [
                    'id'   => (int)$tax->getId(),
                    'name' => $tax->getName(),
                    'rate' => (float)$tax->getRate() / 100,
                ];
            }, $taxes);

            return [
                'data-taxes' => $taxes,
            ];
        };

        /**
         * Choice attributes builder.
         *
         * @param TaxGroupInterface $group
         *
         * @return string
         */
        $choiceLabel = function ($group) use ($defaultCountry) {
            $taxes = $group->getTaxes()->filter(function (TaxInterface $tax) use ($defaultCountry) {
                return $tax->getCountry() === $defaultCountry;
            })->toArray();

            if (!empty($taxes)) {
                $taxes = array_map(function (TaxInterface $tax) {
                    return round($tax->getRate(), 2) . '%';
                }, $taxes);

                return sprintf('%s (%s)', $group->getName(), implode(', ', $taxes));
            }

            return $group->getName();
        };

        $resolver
            ->setDefaults([
                'label'        => function (Options $options, $value) {
                    if (false === $value || !empty($value)) {
                        return $value;
                    }

                    return 'ekyna_commerce.tax_group.label.' . ($options['multiple'] ? 'plural' : 'singular');
                },
                'class'        => $this->taxGroupClass,
                'choice_value' => 'id',
                'choice_attr'  => $choiceAttr,
                'choice_label' => $choiceLabel,
                'attr'         => [
                    'class' => 'tax-group-choice',
                ],
            ])
            ->setNormalizer('attr', function (Options $options, $value) {
                $value = (array)$value;

                if (!isset($value['placeholder'])) {
                    $value['placeholder'] = 'ekyna_commerce.tax_group.label.' . ($options['multiple'] ? 'plural' : 'singular');
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
