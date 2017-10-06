<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemsType extends AbstractType
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $supplierOrderItemRepository;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface $supplierOrderItemRepository
     */
    public function __construct(ResourceRepositoryInterface $supplierOrderItemRepository)
    {
        $this->supplierOrderItemRepository = $supplierOrderItemRepository;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'currency'              => null,
                'entry_type'            => SupplierOrderItemType::class,
                'entry_options'         => [],
                'prototype_data'        => $this->supplierOrderItemRepository->createNew(),
                'add_button_text'       => 'ekyna_commerce.supplier_order.button.add_item',
                'delete_button_confirm' => 'ekyna_commerce.supplier_order.message.confirm_item_removal',
            ])
            ->setAllowedTypes('currency', 'string')
            ->setNormalizer('entry_options', function (Options $options, $value) {
                $value['currency'] = $options['currency'];

                return $value;
            });
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_order_items';
    }
}
