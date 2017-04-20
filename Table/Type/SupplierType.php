<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('email', CType\Column\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 20,
            ])
            ->addColumn('customerCode', CType\Column\TextType::class, [
                'label'    => t('supplier.field.customer_code', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addColumn('currency', CType\Column\TextType::class, [
                'label'         => t('field.currency', [], 'EkynaUi'),
                'property_path' => 'currency.code',
                'position'      => 40,
            ])
            ->addColumn('country', BType\Column\CountryType::class, [
                'label'         => t('field.country', [], 'EkynaUi'),
                'property_path' => 'address.country.code',
                'position'      => 50,
            ])
            ->addColumn('locale', BType\Column\LocaleType::class, [
                'label'    => t('field.locale', [], 'EkynaUi'),
                'position' => 51,
            ])
            ->addColumn('tax', DType\Column\EntityType::class, [
                'label'        => t('tax.label.singular', [], 'EkynaCommerce'),
                'entity_label' => 'name',
                'position'     => 60,
            ])
            ->addColumn('carrier', DType\Column\EntityType::class, [
                'label'        => t('supplier_carrier.label.singular', [], 'EkynaCommerce'),
                'entity_label' => 'name',
                'position'     => 70,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    Action\CreateAction::class => [
                        'resource' => 'ekyna_commerce.supplier_product',
                    ],
                    Action\UpdateAction::class,
                    Action\DeleteAction::class,
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 20,
            ])
            ->addFilter('customerCode', CType\Filter\TextType::class, [
                'label'    => t('supplier.field.customer_code', [], 'EkynaCommerce'),
                'position' => 30,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('resource_summary', true);
    }
}
