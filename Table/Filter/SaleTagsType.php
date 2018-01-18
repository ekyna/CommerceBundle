<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Util\FilterUtil;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Source\AdapterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleTagsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTagsType extends AbstractFilterType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $tagClass;


    /**
     * @inheritDoc
     */
    public function __construct(EntityManagerInterface $em, $tagClass)
    {
        $this->em = $em;
        $this->tagClass = $tagClass;
    }

    /**
     * @inheritDoc
     */
    public function applyFilter(AdapterInterface $adapter, FilterInterface $filter, ActiveFilter $activeFilter, array $options)
    {
        if (!$adapter instanceof EntityAdapter) {
            throw new InvalidArgumentException("Expected instance of " . EntityAdapter::class);
        }

        $parameter = FilterUtil::buildParameterName('tags');
        $operator = $activeFilter->getOperator();
        $value = FilterUtil::buildParameterValue($operator, $activeFilter->getValue());

        $qb = $adapter->getQueryBuilder();
        $orExpr = $qb->expr()->orX();

        foreach (['tags', 'itemsTags'] as $path) {
            $property = $adapter->getQueryBuilderPath($path);
            $orExpr->add(FilterUtil::buildExpression($property, $operator, $parameter));
        }

        $qb->andWhere($orExpr)->setParameter($parameter, $value);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'        => 'ekyna_cms.tag.label.plural',
            'class'        => $this->tagClass,
            'entity_label' => 'name',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
