<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Bundle\CommerceBundle\Form\Type\Subject\SubjectChoiceType;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Extension\Core\Type\Filter\FilterType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Util\FilterOperator;
use Ekyna\Component\Table\View\ActiveFilterView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SaleSubjectType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleSubjectType extends AbstractFilterType
{
    /**
     * @var SubjectProviderRegistryInterface
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param SubjectProviderRegistryInterface $registry
     */
    public function __construct(SubjectProviderRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildActiveView(ActiveFilterView $view, FilterInterface $filter, ActiveFilter $activeFilter, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity $identity */
        $identity = $activeFilter->getValue();

        $provider = $this->registry->getProviderByName($identity->getProvider());

        $subject = $provider->getRepository()->find($identity->getIdentifier());

        $view->vars['value'] = (string) $subject;
    }

    public function buildForm(FormBuilderInterface $builder, FilterInterface $filter, array $options)
    {
        $builder
            ->add('operator', ChoiceType::class, [
                'label'   => false,
                'choices' => FilterOperator::getChoices([
                    FilterOperator::IN,
                    FilterOperator::NOT_IN,
                ]),
            ])
            ->add('value', SubjectChoiceType::class, [
                'label' => false,
            ]);

        return true;
    }

    public function applyFilter(AdapterInterface $adapter, FilterInterface $filter, ActiveFilter $activeFilter, array $options)
    {
        if (!$adapter instanceof EntityAdapter) {
            return false;
        }

        /** @var \Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity $identity */
        $identity = $activeFilter->getValue();
        $qb = $adapter->getQueryBuilder();
        $alias = $qb->getRootAliases()[0];
        $qb
            ->join($alias.'.items', 'i')
            ->leftJoin('i.children', 'c')
            ->leftJoin('c.children', 'sc')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('i.subjectIdentity.provider', ':provider'),
                    $qb->expr()->eq('i.subjectIdentity.identifier', ':identifier')
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('c.subjectIdentity.provider', ':provider'),
                    $qb->expr()->eq('c.subjectIdentity.identifier', ':identifier')
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('sc.subjectIdentity.provider', ':provider'),
                    $qb->expr()->eq('sc.subjectIdentity.identifier', ':identifier')
                )
            ))
            ->setParameters([
                'provider'   => $identity->getProvider(),
                'identifier' => $identity->getIdentifier(),
            ]);

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