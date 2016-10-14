<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderComposeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderComposeType extends AbstractType
{
    /**
     * @var string
     */
    protected $supplierProductClass;


    /**
     * Constructor.
     *
     * @param $supplierProductClass
     */
    public function __construct($supplierProductClass)
    {
        $this->supplierProductClass = $supplierProductClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @param EntityRepository $repository
         *
         * @return \Doctrine\ORM\QueryBuilder
         */
        $queryBuilder = function (EntityRepository $repository) use ($options) {
            $qb = $repository->createQueryBuilder('sp');

            return $qb
                ->andWhere($qb->expr()->eq('sp.supplier', ':supplier'))
                ->setParameter('supplier', $options['supplier']);
        };

        /** @noinspection PhpUnusedParameterInspection */
        /**
         * @param \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $value
         * @param mixed                                                             $key
         * @param int                                                               $index
         *
         * @return string
         */
        $choiceLabel = function ($value, $key, $index) {
            return sprintf(
                '[%s] %s (%s - %s€) ',
                $value->getReference(),
                $value->getDesignation(),
                round($value->getAvailableStock()),
                number_format($value->getNetPrice(), 2, ',', '')
            );
        };

        /** @noinspection PhpUnusedParameterInspection */
        /**
         * @param \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $value
         * @param mixed                                                             $key
         * @param int                                                               $index
         *
         * @return string
         */
        $choiceAttributes = function ($value, $key, $index) {
            return [
                'data-designation'  => $value->getDesignation(),
                'data-reference'    => $value->getReference(),
                'data-net-price'    => $value->getNetPrice(),
            ];
        };

        $builder
            ->add('items', SupplierOrderItemsType::class, [
                'attr' => [
                    'class' => 'order-compose-items',
                ],
            ])
            ->add('quickAddSelect', EntityType::class, [
                'label'         => 'ekyna_commerce.supplier_product.label.singular',
                'class'         => $this->supplierProductClass,
                'query_builder' => $queryBuilder,
                'choice_label'  => $choiceLabel,
                'choice_attr'   => $choiceAttributes,
                'placeholder'   => false,
                'required'      => false,
                'mapped'        => false,
                'attr'          => [
                    'class' => 'order-compose-quick-add-select',
                ],
            ])
            ->add('quickAddButton', ButtonType::class, [
                'label' => 'ekyna_core.button.add',
                'attr'  => [
                    'class' => 'order-compose-quick-add-button',
                ],
            ]);

        /*$builder
            ->add('quickAdd', EntitySearchType::class, [
                'label'           => 'ekyna_commerce.supplier_product.label.singular',
                'class'           => $this->supplierProductClass,
                'search_route'    => 'ekyna_commerce_supplier_product_admin_search',
                'find_route'      => 'ekyna_commerce_supplier_product_admin_find',
                'allow_clear'     => false,
                'format_function' =>
                    "if(!data.id)return 'Rechercher';" .
                    "return $('<span>'+data.designation+'</span>');",
                'required'        => false,
                'mapped'          => false,
            ]);*/
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('supplier')
            ->setDefaults([
                'label'        => false,
                'inherit_data' => true,
                'attr'         => [
                    'class' => 'commerce-supplier-order-compose',
                ],
            ])
            ->setAllowedTypes('supplier', SupplierInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_order_compose';
    }

}
