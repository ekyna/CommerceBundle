<?php

declare(strict_types=1);

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
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'line_type' => OrderInvoiceLineType::class,
            'item_type' => OrderInvoiceItemType::class,
        ]);
    }
}
