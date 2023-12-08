<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\RelayPointType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodPickType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_unshift;
use function Symfony\Component\Translation\t;

/**
 * Class SaleTransformType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comment', Type\TextareaType::class, [
                'label'    => t('field.comment', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => $options['message'],
                'attr'        => ['align_with_widget' => true],
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new Assert\IsTrue(),
                ],
            ]);

        if ($options['admin_mode']) {
            $builder
                ->add('currency', CurrencyChoiceType::class)
                ->add('locale', LocaleChoiceType::class)
                ->add('autoShipping', Type\CheckboxType::class, [
                    'label'    => t('sale.field.auto_shipping', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('autoDiscount', Type\CheckboxType::class, [
                    'label'    => t('sale.field.auto_discount', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('autoNotify', Type\CheckboxType::class, [
                    'label'    => t('sale.field.auto_notify', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('taxExempt', Type\CheckboxType::class, [
                    'label'    => t('sale.field.tax_exempt', [], 'EkynaCommerce'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('title', Type\TextType::class, [
                    'label'    => t('field.title', [], 'EkynaUi'),
                    'required' => false,
                ])
                ->add('voucherNumber', Type\TextType::class, [
                    'label'    => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                    'required' => false,
                ])
                ->add('originNumber', Type\TextType::class, [
                    'label'    => t('sale.field.origin_number', [], 'EkynaCommerce'),
                    'required' => false,
                ])
                ->add('description', Type\TextareaType::class, [
                    'label'    => t('field.description', [], 'EkynaCommerce'),
                    'required' => false,
                ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            /** @var SaleInterface $sale */
            $sale = $event->getData();
            $form = $event->getForm();

            if ($options['admin_mode']) {
                $customer = $sale->getCustomer();
                if ($sale instanceof OrderInterface) {
                    $form->add('sample', CheckboxType::class, [
                        'label'    => t('field.sample', [], 'EkynaCommerce'),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
                }
                if (null !== $customer) {
                    if ($customer->hasParent()) {
                        $choices = [$customer, $customer->getParent()];
                    } elseif ($customer->hasChildren()) {
                        $choices = $customer->getChildren()->toArray();
                        array_unshift($choices, $customer);
                    } else {
                        return;
                    }

                    $form->add('customer', Type\ChoiceType::class, [
                        'label'                     => t('customer.label.singular', [], 'EkynaCommerce'),
                        'choices'                   => $choices,
                        'choice_value'              => 'id',
                        'choice_label'              => function ($customer) {
                            return (string)$customer;
                        },
                        'choice_translation_domain' => false,
                        'help'                      => t('customer.help.hierarchy', [], 'EkynaCommerce'),
                        'help_html'                 => true,
                        'select2'                   => false,
                    ]);
                }
            } else {
                $form
                    ->add('shipmentMethod', ShipmentMethodPickType::class, [
                        'subject'  => $sale,
                        'available' => !$options['admin_mode'],
                        'expanded' => false,
                        'attr'     => [
                            'class' => 'sale-shipment-method',
                        ],
                    ])
                    ->add('relayPoint', RelayPointType::class, [
                        'search' => $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress(),
                    ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => SaleInterface::class,
                'message'    => null,
            ])
            ->setAllowedTypes('message', [TranslatableInterface::class, 'string']);
    }
}
