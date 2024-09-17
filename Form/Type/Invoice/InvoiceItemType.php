<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InvoiceItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceItemType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var InvoiceInterface $invoice */
        $invoice = $options['invoice'];

        $builder
            ->add('designation', Type\TextType::class, [
                'label'          => t('field.designation', [], 'EkynaUi'),
                'attr'           => [
                    'placeholder' => t('field.designation', [], 'EkynaUi'),
                ],
                'error_bubbling' => true,
            ])
            ->add('reference', Type\TextType::class, [
                'label'          => t('field.reference', [], 'EkynaUi'),
                'attr'           => [
                    'placeholder' => t('field.reference', [], 'EkynaUi'),
                ],
                'error_bubbling' => true,
            ])
            ->add('unit', PriceType::class, [
                'label'          => t('sale.field.net_unit', [], 'EkynaCommerce'),
                'currency'       => $invoice->getCurrency(),
                'attr'           => [
                    'placeholder' => t('sale.field.net_unit', [], 'EkynaCommerce'),
                ],
                'error_bubbling' => true,
            ])
            // TODO Use \Ekyna\Bundle\CommerceBundle\Form\FormHelper::addQuantityType
            ->add('quantity', Type\NumberType::class, [
                'label'          => t('field.quantity', [], 'EkynaUi'),
                'scale'          => 3, // TODO Packaging format
                'decimal'        => true,
                'disabled'       => $options['disabled'],
                'attr'           => [
                    'class' => 'input-sm',
                ],
                'error_bubbling' => true,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'attr'           => [
                    'placeholder' => t('field.tax_group', [], 'EkynaCommerce'),
                ],
                'error_bubbling' => true,
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['invoice'])
            ->setAllowedTypes('invoice', InvoiceInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_invoice_item';
    }
}
