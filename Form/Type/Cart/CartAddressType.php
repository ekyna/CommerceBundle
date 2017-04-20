<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CartAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddressType extends AbstractResourceType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('coordinate', false);
    }

    public function getParent(): ?string
    {
        return AddressType::class;
    }
}
