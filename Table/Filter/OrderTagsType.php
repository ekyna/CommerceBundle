<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Table\Extension\Core\Type\Filter\EntitiesType;
use Ekyna\Component\Table\Util\FilterOperator;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderTagsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderTagsType extends EntitiesType
{
    /**
     * @var string
     */
    private $tagClass;


    /**
     * @inheritDoc
     */
    public function __construct(EntityManagerInterface $em, $tagClass)
    {
        parent::__construct($em);

        $this->tagClass = $tagClass;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label'    => 'ekyna_cms.tag.label.plural',
            'class'    => $this->tagClass,
            'property' => 'name',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function applyFilter(QueryBuilder $qb, array $data, array $options)
    {
        self::$filterCount++;
        $alias = $qb->getRootAliases()[0];
        $qb
            ->andWhere($qb->expr()->orX(
                FilterOperator::buildExpression(
                    $alias . '.tags',
                    $data['operator'],
                    ':filter_' . self::$filterCount
                ),
                FilterOperator::buildExpression(
                    $alias . '.itemsTags',
                    $data['operator'],
                    ':filter_' . self::$filterCount
                )
            ))
            ->setParameter(
                'filter_' . self::$filterCount,
                FilterOperator::buildParameter($data['operator'], $data['value'])
            );
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_order_tags';
    }
}
