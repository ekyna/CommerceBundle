<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var SupplierProductInterface $product */
            $product = $event->getData();

            // Step 1: Supplier is not selected
            if (null === $supplier = $product->getSupplier()) {
                $form->add('supplier', SupplierChoiceType::class);

                return;
            }

            if (null === $currency = $supplier->getCurrency()) {
                throw new LogicException("Supplier's currency must be set at this point.");
            }

            $form
                ->add('supplier', SupplierChoiceType::class, [
                    'disabled' => true,
                    /* TODO (?) 'attr'     => [
                        'class' => 'order-supplier',
                    ],*/
                ])
                ->add('designation', Symfony\TextType::class, [
                    'label' => t('field.designation', [], 'EkynaUi'),
                ])
                ->add('reference', Symfony\TextType::class, [
                    'label'    => t('field.reference', [], 'EkynaUi'),
                ])
                ->add('netPrice', Symfony\MoneyType::class, [
                    'label'    => t('field.net_price', [], 'EkynaCommerce'),
                    'currency' => $currency->getCode(),
                    'decimal'  => true,
                    'scale'    => 5,
                    'help'     => t('supplier_product.help.net_price', [], 'EkynaCommerce'),
                ])
                ->add('taxGroup', TaxGroupChoiceType::class)
                ->add('physical', Symfony\CheckboxType::class, [
                    'label'    => t('field.physical', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('weight', Symfony\NumberType::class, [
                    'label'   => t('field.weight', [], 'EkynaUi'),
                    'decimal' => true,
                    'scale'   => 3,
                    'attr'    => [
                        'input_group' => ['append' => 'Kg'],
                    ],
                ])
                ->add('availableStock', Symfony\NumberType::class, [
                    'label'    => t('field.available_stock', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'scale'    => 3, // TODO Packaging format
                ])
                ->add('orderedStock', Symfony\NumberType::class, [
                    'label'    => t('supplier_product.field.ordered_stock', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'scale'    => 3, // TODO Packaging format
                ])
                ->add('packing', Symfony\NumberType::class, [
                    'label'    => t('field.packing', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'scale'    => 3, // TODO Packaging format
                ])
                ->add('estimatedDateOfArrival', Symfony\DateType::class, [
                    'label'    => t('field.replenishment_eda', [], 'EkynaCommerce'),
                    'required' => false,
                ])
                ->add('subjectIdentity', Commerce\Subject\SubjectChoiceType::class, [
                    'label'     => t('subject.label.singular', [], 'EkynaCommerce'),
                    'lock_mode' => true,
                    'required'  => false,
                    'context'   => SubjectProviderInterface::CONTEXT_SUPPLIER,
                ]);
        });
    }
}
