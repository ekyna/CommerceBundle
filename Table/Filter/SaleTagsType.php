<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Filter;

use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntityAdapter;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Filter\EntityType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Util\FilterUtil;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Ekyna\Component\Table\Filter\FilterInterface;
use Ekyna\Component\Table\Source\AdapterInterface;
use Ekyna\Component\Table\Util\FilterOperator;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SaleTagsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Filter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTagsType extends AbstractFilterType
{
    private string $tagClass;

    public function __construct(string $tagClass)
    {
        $this->tagClass = $tagClass;
    }

    public function applyFilter(
        AdapterInterface $adapter,
        FilterInterface $filter,
        ActiveFilter $activeFilter,
        array $options
    ): bool {
        if (!$adapter instanceof EntityAdapter) {
            throw new UnexpectedTypeException($adapter, EntityAdapter::class);
        }

        $parameter = FilterUtil::buildParameterName('tags');
        $operator = $activeFilter->getOperator();
        $value = FilterUtil::buildParameterValue($operator, $activeFilter->getValue());

        $qb = $adapter->getQueryBuilder();

        $clause = $operator === FilterOperator::IN || $operator === FilterOperator::MEMBER
            ? $qb->expr()->orX() : $qb->expr()->andX();

        foreach (['tags', 'itemsTags', 'customer.tags'] as $path) {
            $property = $adapter->getQueryBuilderPath($path);
            $clause->add(FilterUtil::buildExpression($property, $operator, $parameter));
        }

        $qb->andWhere($clause)->setParameter($parameter, $value);

        return true;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'        => t('tag.label.plural', [], 'EkynaCms'),
            'class'        => $this->tagClass,
            'entity_label' => 'name',
        ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
