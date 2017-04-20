<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType as PhoneType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Extension\Core\Type\Filter\FilterType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Util\FilterOperator;
use Ekyna\Component\Table\View\ActiveFilterView;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class PhoneNumberType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PhoneNumberType extends AbstractFilterType
{
    public function buildForm(FormBuilderInterface $builder, FilterInterface $filter, array $options): bool
    {
        $builder
            ->add('operator', FormType\ChoiceType::class, [
                'label'                     => false,
                'required'                  => true,
                'choices'                   => FilterOperator::getChoices([FilterOperator::EQUAL]),
                'choice_translation_domain' => false,
            ])
            ->add('value', PhoneType::class, [
                'label'       => false,
                'required'    => true,
                'constraints' => [
                    new NotNull(),
                ],
            ]);

        return true;
    }

    public function buildActiveView(
        ActiveFilterView $view,
        FilterInterface  $filter,
        ActiveFilter     $activeFilter,
        array            $options
    ): void {
        /** @var PhoneNumber $value */
        $number = $activeFilter->getValue();
        $util = PhoneNumberUtil::getInstance();

        $view->vars['value'] = $util->format($number, PhoneNumberFormat::INTERNATIONAL);
    }

    public function applyFilter(
        AdapterInterface $adapter,
        FilterInterface  $filter,
        ActiveFilter     $activeFilter,
        array            $options
    ): bool {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        $util = PhoneNumberUtil::getInstance();

        /** @var PhoneNumber $value */
        $number = $activeFilter->getValue();
        $qb = $adapter->getQueryBuilder();
        $alias = $qb->getRootAliases()[0];

        $qb
            ->join($alias . '.addresses', 'ca')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq($alias . '.phone', ':number'),
                $qb->expr()->eq($alias . '.mobile', ':number'),
                $qb->expr()->eq('ca.phone', ':number'),
                $qb->expr()->eq('ca.mobile', ':number')
            ))
            ->setParameter('number', $util->format($number, PhoneNumberFormat::E164));

        return true;
    }

    public function getParent(): ?string
    {
        return FilterType::class;
    }
}
