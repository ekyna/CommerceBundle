<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Extension\Core\Type\Filter\FilterType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Util\FilterOperator;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType as PhoneType;
use Ekyna\Component\Table\View\ActiveFilterView;
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
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, FilterInterface $filter, array $options)
    {
        $builder
            ->add('operator', FormType\ChoiceType::class, [
                'label'   => false,
                'required' => true,
                'choices' => FilterOperator::getChoices([FilterOperator::EQUAL]),
            ])
            ->add('value', PhoneType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ],
            ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildActiveView(ActiveFilterView $view, FilterInterface $filter, ActiveFilter $activeFilter, array $options)
    {
        /** @var \libphonenumber\PhoneNumber $value */
        $number = $activeFilter->getValue();
        $util = PhoneNumberUtil::getInstance();

        $view->vars['value'] = $util->format($number, PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * @inheritDoc
     */
    public function applyFilter(
        AdapterInterface $adapter,
        FilterInterface $filter,
        ActiveFilter $activeFilter,
        array $options
    ) {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        $util = PhoneNumberUtil::getInstance();

        /** @var \libphonenumber\PhoneNumber $value */
        $number = $activeFilter->getValue();
        $qb = $adapter->getQueryBuilder();
        $alias = $qb->getRootAliases()[0];

        $qb
            ->join($alias . '.addresses', 'ca')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq($alias.'.phone', ':number'),
                $qb->expr()->eq($alias.'.mobile', ':number'),
                $qb->expr()->eq('ca.phone', ':number'),
                $qb->expr()->eq('ca.mobile', ':number')
            ))
            ->setParameter('number', $util->format($number, PhoneNumberFormat::E164));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return FilterType::class;
    }
}
