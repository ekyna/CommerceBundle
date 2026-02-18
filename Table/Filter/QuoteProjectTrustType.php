<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteProjectTrustType as TrustChoiceType;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Extension\Core\Type\Filter\FilterType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Util\FilterOperator;
use Ekyna\Component\Table\View\ActiveFilterView;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use function array_search;

/**
 * Class QuoteProjectTrustType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteProjectTrustType extends AbstractFilterType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, FilterInterface $filter, array $options): bool
    {
        $builder
            ->add('operator', Form\ChoiceType::class, [
                'label'       => false,
                'required'    => true,
                'choices'     => FilterOperator::getChoices([
                    FilterOperator::NOT_EQUAL,
                    FilterOperator::EQUAL,
                    FilterOperator::LOWER_THAN,
                    FilterOperator::LOWER_THAN_OR_EQUAL,
                    FilterOperator::GREATER_THAN,
                    FilterOperator::GREATER_THAN_OR_EQUAL,
                ]),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('value', TrustChoiceType::class, [
                'label'       => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildActiveView(
        ActiveFilterView $view,
        FilterInterface  $filter,
        ActiveFilter     $activeFilter,
        array            $options
    ): void {
        $value = $activeFilter->getValue();

        if (false !== $key = array_search($value, TrustChoiceType::CHOICES)) {
            $view->vars['value'] = $key;
        } else {
            $view->vars['value'] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
