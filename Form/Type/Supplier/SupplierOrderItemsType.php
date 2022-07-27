<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierOrderItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemsType extends AbstractType
{
    public function __construct(private readonly ResourceFactoryInterface $factory)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'currency'              => null,
                'entry_type'            => SupplierOrderItemType::class,
                'entry_options'         => [],
                'prototype_data'        => $this->factory->create(),
                'add_button_text'       => t('supplier_order.button.add_item', [], 'EkynaCommerce'),
                'delete_button_confirm' => t('supplier_order.message.confirm_item_removal', [], 'EkynaCommerce'),
            ])
            ->setAllowedTypes('currency', 'string')
            ->setNormalizer('entry_options', function (Options $options, $value) {
                $value['currency'] = $options['currency'];

                return $value;
            });
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_supplier_order_items';
    }
}
