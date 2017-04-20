<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MoneyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\WarehouseChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierOrderSubmitType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderSubmitType extends Form\AbstractType
{
    private string $orderClass;

    public function __construct(string $orderClass)
    {
        $this->orderClass = $orderClass;
    }

    public function buildForm(Form\FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emails', CollectionType::class, [
                'label'         => t('field.recipients', [], 'EkynaUi'),
                'required'      => false,
                'entry_type'    => Type\EmailType::class,
                'entry_options' => [
                    'label' => false,
                    'attr'  => [
                        'widget_col' => 12,
                    ],
                ],
            ])
            ->add('subject', Type\TextType::class, [
                'label'    => t('field.subject', [], 'EkynaUi'),
                'required' => true,
                'attr'     => [
                    'class' => 'notify-subject',
                ],
            ])
            ->add('message', TinymceType::class, [
                'label'    => t('field.message', [], 'EkynaUi'),
                'theme'    => 'front',
                'required' => true,
                'attr'     => [
                    'class' => 'notify-message',
                ],
            ])
            ->add('confirm', Type\CheckboxType::class, [
                'label' => t('message.action_confirm', [], 'EkynaUi'),
                'attr'  => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('sendEmail', Type\CheckboxType::class, [
                'label'    => t('supplier_order.field.send_email', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('sendLabels', Type\CheckboxType::class, [
                'label'    => t('supplier_order.field.send_labels', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add($builder
                ->create('order', Type\FormType::class, [
                    'data_class' => $this->orderClass,
                ])
            );

        $builder
            ->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options): void {
                /** @var SupplierOrderSubmit $data */
                $data = $event->getData();
                $form = $event->getForm();

                $order = $data->getOrder();
                if (null === $order) {
                    throw new InvalidArgumentException('Supplier order must be set');
                }

                $currency = $order->getCurrency();

                $hasCarrier = null !== $order->getCarrier();

                $form
                    ->add('template', SupplierTemplateChoiceType::class, [
                        'order' => $order,
                    ])
                    ->get('order')
                    ->add('estimatedDateOfArrival', Type\DateType::class, [
                        'label'    => t('field.estimated_date_of_arrival', [], 'EkynaCommerce'),
                        'required' => SupplierOrderStates::isStockableState($order),
                    ])
                    ->add('carrier', ResourceChoiceType::class, [
                        'resource'  => 'ekyna_commerce.supplier_carrier',
                        'allow_new' => true,
                        'attr'      => [
                            'class' => 'order-carrier',
                        ],
                    ])
                    ->add('warehouse', WarehouseChoiceType::class, [
                        'attr' => [
                            'class' => 'order-warehouse',
                        ],
                    ])
                    // Supplier fields
                    ->add('shippingCost', MoneyType::class, [
                        'label' => t('supplier_order.field.shipping_cost', [], 'EkynaCommerce'),
                        'base'  => $currency,
                    ])
                    ->add('discountTotal', MoneyType::class, [
                        'label' => t('supplier_order.field.discount_total', [], 'EkynaCommerce'),
                        'base'  => $currency,
                    ])
                    ->add('taxTotal', MoneyType::class, [
                        'label'    => t('supplier_order.field.tax_total', [], 'EkynaCommerce'),
                        'base'     => $currency,
                        'disabled' => true,
                    ])
                    ->add('paymentTotal', MoneyType::class, [
                        'label'    => t('supplier_order.field.payment_total', [], 'EkynaCommerce'),
                        'base'     => $currency,
                        'disabled' => true,
                    ])
                    ->add('paymentDate', Type\DateType::class, [
                        'label'    => t('supplier_order.field.payment_date', [], 'EkynaCommerce'),
                        'required' => false,
                    ])
                    ->add('paymentDueDate', Type\DateType::class, [
                        'label'    => t('supplier_order.field.payment_due_date', [], 'EkynaCommerce'),
                        'required' => false,
                    ])
                    // Forwarder
                    ->add('forwarderFee', MoneyType::class, [
                        'label'    => t('supplier_order.field.forwarder_fee', [], 'EkynaCommerce'),
                        'disabled' => !$hasCarrier,
                    ])
                    ->add('customsTax', MoneyType::class, [
                        'label'    => t('supplier_order.field.customs_tax', [], 'EkynaCommerce'),
                        'disabled' => !$hasCarrier,
                    ])
                    ->add('customsVat', MoneyType::class, [
                        'label'    => t('supplier_order.field.customs_vat', [], 'EkynaCommerce'),
                        'disabled' => !$hasCarrier,
                    ])
                    ->add('forwarderTotal', MoneyType::class, [
                        'label'    => t('supplier_order.field.forwarder_total', [], 'EkynaCommerce'),
                        'disabled' => true,
                    ])
                    ->add('forwarderDate', Type\DateType::class, [
                        'label'    => t('supplier_order.field.forwarder_date', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => !$hasCarrier,
                    ])
                    ->add('forwarderDueDate', Type\DateType::class, [
                        'label'    => t('supplier_order.field.forwarder_due_date', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => !$hasCarrier,
                    ])
                    // Tracking
                    ->add('trackingUrls', CollectionType::class, [
                        'label'         => t('supplier_order.field.tracking_urls', [], 'EkynaCommerce'),
                        'entry_type'    => Type\UrlType::class,
                        'entry_options' => ['required' => true],
                        'required'      => false,
                        'allow_add'     => true,
                        'allow_delete'  => true,
                    ])
                    ->add('description', Type\TextareaType::class, [
                        'label'    => t('field.description', [], 'EkynaCommerce'),
                        'required' => false,
                    ]);
            });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-supplier-submit');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', SupplierOrderSubmit::class);
    }
}
