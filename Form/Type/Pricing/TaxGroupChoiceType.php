<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Pricing;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function implode;
use function round;
use function sprintf;

/**
 * Class TaxGroupChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupChoiceType extends AbstractType
{
    private CountryRepositoryInterface $countryRepository;


    public function __construct(CountryRepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaultCountry = $this->countryRepository->findDefault();

        $choiceAttr = function (TaxGroupInterface $group) use ($defaultCountry): array {
            $taxes = $group->getTaxes()->filter(function (TaxInterface $tax) use ($defaultCountry) {
                return $tax->getCountry() === $defaultCountry;
            })->toArray();

            $taxes = array_map(function (TaxInterface $tax) {
                return [
                    'id'   => $tax->getId(),
                    'name' => $tax->getName(),
                    'rate' => $tax->getRate()->div(100)->toFixed(3),
                ];
            }, $taxes);

            return [
                'data-taxes' => $taxes,
            ];
        };

        $choiceLabel = function (TaxGroupInterface $group) use ($defaultCountry): string {
            $taxes = $group->getTaxes()->filter(function (TaxInterface $tax) use ($defaultCountry) {
                return $tax->getCountry() === $defaultCountry;
            })->toArray();

            if (!empty($taxes)) {
                $taxes = array_map(function (TaxInterface $tax) {
                    return $tax->getRate()->toFixed(1) . '%';
                }, $taxes);

                return sprintf('%s (%s)', $group->getName(), implode(', ', $taxes));
            }

            return $group->getName();
        };

        $resolver
            ->setDefaults([
                'resource'     => 'ekyna_commerce.tax_group',
                'choice_value' => 'id',
                'choice_attr'  => $choiceAttr,
                'choice_label' => $choiceLabel,
                'select2'      => false,
                'attr'         => [
                    'class' => 'tax-group-choice',
                ],
            ]);
    }

    public function getParent(): ?string
    {
        return ResourceChoiceType::class;
    }
}
