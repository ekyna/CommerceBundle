<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceItemsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'          => 'ekyna_commerce.invoice.field.items',
            'prototype_name' => '__item__',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_items';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
