<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierProductType
 *
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductType extends AbstractResourceType
{
    /**
     * Builds the table for the given subject.
     */
    private function buildForSubject(TableBuilderInterface $builder, SubjectInterface $subject): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            return;
        }

        $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($subject): void {
            $qb
                ->andWhere($qb->expr()->eq($alias . '.subjectIdentity.provider', ':provider'))
                ->andWhere($qb->expr()->eq($alias . '.subjectIdentity.identifier', ':identifier'))
                ->setParameter('provider', $subject::getProviderName())
                ->setParameter('identifier', $subject->getId());
        });

        $builder->setPerPageChoices([100]);
    }

    /**
     * Builds the table for the given supplier.
     */
    private function buildForSupplier(TableBuilderInterface $builder, SupplierInterface $supplier): void
    {
        $builder->setExportable(true);

        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            return;
        }

        $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($supplier): void {
            $qb
                ->andWhere($qb->expr()->eq($alias . '.supplier', ':supplier'))
                ->setParameter('supplier', $supplier);
        });
    }

    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $subject = $options['subject'];
        $supplier = $options['supplier'];

        if ($subject && $supplier) {
            throw new InvalidArgumentException("Please provider 'subject' or 'supplier' option, but not both.");
        }

        if (null !== $subject) {
            $this->buildForSubject($builder, $subject);
        } else {
            $builder
                ->addColumn('designation', BType\Column\AnchorType::class, [
                    'label'    => t('field.designation', [], 'EkynaUi'),
                    'position' => 0,
                ])
                ->addFilter('designation', CType\Filter\TextType::class, [
                    'label'    => t('field.designation', [], 'EkynaUi'),
                    'position' => 10,
                ]);
        }

        if (null !== $supplier) {
            $this->buildForSupplier($builder, $supplier);
        } else {
            $builder
                ->addColumn('supplier', BType\Column\AnchorType::class, [
                    'label'    => t('supplier.label.singular', [], 'EkynaCommerce'),
                    'position' => 0,
                ])
                ->addFilter('supplier', ResourceType::class, [
                    'resource' => 'ekyna_commerce.supplier',
                    'position' => 10,
                ]);
        }

        $builder
            ->addDefaultSort('id', ColumnSort::DESC)
            ->addColumn('reference', CType\Column\TextType::class, [
                'label'          => t('field.reference', [], 'EkynaUi'),
                'clipboard_copy' => true,
                'sortable'       => true,
                'position'       => 20,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'         => t('field.buy_net_price', [], 'EkynaCommerce'),
                'currency_path' => 'supplier.currency.code',
                'scale'         => 5,
                'sortable'      => true,
                'position'      => 30,
            ])
            ->addColumn('taxGroup', BType\Column\AnchorType::class, [
                'label'    => t('tax_group.label.singular', [], 'EkynaCommerce'),
                'position' => 40,
            ])
            ->addColumn('weight', CType\Column\NumberType::class, [
                'label'     => t('field.weight', [], 'EkynaUi'),
                'precision' => 3,
                'append'    => 'kg',
                'sortable'  => true,
                'position'  => 50,
            ])
            ->addColumn('packing', CType\Column\NumberType::class, [
                'label'     => t('field.packing', [], 'EkynaCommerce'),
                'precision' => 0,
                'sortable'  => true,
                'position'  => 60,
            ])
            ->addColumn('availableStock', CType\Column\NumberType::class, [
                'label'     => t('supplier_product.field.available', [], 'EkynaCommerce'),
                'precision' => 0,
                'sortable'  => true,
                'position'  => 70,
            ])
            ->addColumn('orderedStock', CType\Column\NumberType::class, [
                'label'     => t('supplier_product.field.ordered', [], 'EkynaCommerce'),
                'precision' => 0,
                'sortable'  => true,
                'position'  => 80,
            ])
            ->addColumn('estimatedDateOfArrival', CType\Column\DateTimeType::class, [
                'label'       => t('supplier_product.field.eda', [], 'EkynaCommerce'),
                'time_format' => 'none',
                'sortable'    => true,
                'position'    => 90,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ])
            ->addFilter('reference', CType\Filter\TextType::class, [
                'label'    => t('field.reference', [], 'EkynaUi'),
                'position' => 20,
            ])
            ->addFilter('netPrice', CType\Filter\NumberType::class, [
                'label'    => t('field.buy_net_price', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addFilter('taxGroup', ResourceType::class, [
                'resource'     => 'ekyna_commerce.tax_group',
                'trans_domain' => 'EkynaCommerce',
                'position'     => 40,
            ])
            ->addFilter('weight', CType\Filter\NumberType::class, [
                'label'    => t('field.weight', [], 'EkynaUi'),
                'position' => 50,
            ])
            ->addFilter('packing', CType\Filter\NumberType::class, [
                'label'    => t('field.packing', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addFilter('availableStock', CType\Filter\NumberType::class, [
                'label'    => t('field.available_stock', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            ->addFilter('orderedStock', CType\Filter\NumberType::class, [
                'label'    => t('supplier_product.field.ordered_stock', [], 'EkynaCommerce'),
                'position' => 80,
            ])
            ->addFilter('estimatedDateOfArrival', CType\Filter\DateTimeType::class, [
                'label'    => t('field.replenishment_eda', [], 'EkynaCommerce'),
                'position' => 90,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('subject', null)
            ->setDefault('supplier', null)
            ->setAllowedTypes('subject', ['null', SubjectInterface::class])
            ->setAllowedTypes('supplier', ['null', SupplierInterface::class]);
    }
}
