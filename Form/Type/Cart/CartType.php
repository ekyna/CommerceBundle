<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Cart;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleAddressType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
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
            ->add('invoiceAddress', SaleAddressType::class, [
                'label'          => t('sale.field.invoice_address', [], 'EkynaCommerce'),
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'mode'           => SaleAddressType::MODE_INVOICE,
                'customer_field' => 'customer',
                'required'       => true,
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => t('field.expires_at', [], 'EkynaUi'),
            ]);

        FormUtil::bindFormEventsToChildren(
            $builder,
            [
                FormEvents::POST_SET_DATA => 0,
                FormEvents::PRE_SET_DATA  => 2048,
                FormEvents::PRE_SUBMIT    => 2048,
                FormEvents::POST_SUBMIT   => 2048,
            ],
            ['invoiceAddress']
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', CartAddressType::class);
    }
}
