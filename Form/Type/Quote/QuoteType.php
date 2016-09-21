<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends SaleType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', QuoteAddressType::class);
    }
}
