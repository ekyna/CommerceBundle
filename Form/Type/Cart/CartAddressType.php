<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CartAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddressType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('coordinate', false);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
