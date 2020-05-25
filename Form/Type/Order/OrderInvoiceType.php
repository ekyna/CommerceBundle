<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Invoice\InvoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends InvoiceType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'line_type' => OrderInvoiceLineType::class,
            'item_type' => OrderInvoiceItemType::class,
        ]);
    }
}
