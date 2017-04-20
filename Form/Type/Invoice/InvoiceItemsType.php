<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InvoiceItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceItemsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'          => t('invoice.field.items', [], 'EkynaCommerce'),
            'prototype_name' => '__item__',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_invoice_items';
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
