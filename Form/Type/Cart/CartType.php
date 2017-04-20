<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CartType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartType extends SaleType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('expiresAt', DateTimeType::class, [
                'label' => t('field.expires_at', [], 'EkynaUi'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', CartAddressType::class);
    }
}
