<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceItemType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var InvoiceInterface $invoice */
        $invoice = $options['invoice'];

        $builder
            ->add('designation', Type\TextType::class, [
                'label'          => 'ekyna_core.field.designation',
                'attr'           => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
                'error_bubbling' => true,
            ])
            ->add('reference', Type\TextType::class, [
                'label'          => 'ekyna_core.field.reference',
                'attr'           => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
                'error_bubbling' => true,
            ])
            ->add('unit', PriceType::class, [
                'label'          => 'ekyna_commerce.sale.field.net_unit',
                'currency'       => $invoice->getCurrency(),
                'attr'           => [
                    'placeholder' => 'ekyna_commerce.sale.field.net_unit',
                ],
                'error_bubbling' => true,
            ])
            ->add('quantity', Type\NumberType::class, [
                'label'          => 'ekyna_core.field.quantity',
                'disabled'       => $options['disabled'],
                'attr'           => [
                    'class' => 'input-sm',
                ],
                'error_bubbling' => true,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'attr'           => [
                    'placeholder' => 'ekyna_commerce.field.tax_group',
                ],
                'error_bubbling' => true,
            ])
            ->add('position', CollectionPositionType::class);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['invoice'])
            ->setAllowedTypes('invoice', InvoiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_item';
    }
}
