<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MoneyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\WarehouseChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderSubmitType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderSubmitType extends Form\AbstractType
{
    /**
     * @var string
     */
    private $orderClass;


    /**
     * Constructor.
     *
     * @param string $orderClass
     */
    public function __construct(string $orderClass)
    {
        $this->orderClass = $orderClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('emails', CollectionType::class, [
                'label'         => 'ekyna_core.field.recipients',
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
                'label'    => 'ekyna_core.field.subject',
                'required' => true,
                'attr'     => [
                    'class' => 'notify-subject',
                ],
            ])
            ->add('message', TinymceType::class, [
                'label'    => 'ekyna_core.field.message',
                'theme'    => 'front',
                'required' => true,
                'attr'     => [
                    'class' => 'notify-message',
                ],
            ])
            ->add('confirm', Type\CheckboxType::class, [
                'label' => 'ekyna_core.message.action_confirm',
                'attr'  => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('sendEmail', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.send_email',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('sendLabels', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.send_labels',
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

        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            /** @var SupplierOrderSubmit $data */
            $data = $event->getData();
            $form = $event->getForm();

            $order = $data->getOrder();
            if (null === $order) {
                throw new InvalidArgumentException("Supplier order must be set");
            }

            $currency = $order->getCurrency();

            $hasCarrier = null !== $order->getCarrier();

            $form
                ->add('template', SupplierTemplateChoiceType::class, [
                    'order' => $order,
                ])
                ->get('order')
                ->add('estimatedDateOfArrival', Type\DateTimeType::class, [
                    'label'    => 'ekyna_commerce.field.estimated_date_of_arrival',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => SupplierOrderStates::isStockableState($order),
                ])
                ->add('carrier', SupplierCarrierChoiceType::class, [
                    'allow_new' => true,
                    'attr'      => [
                        'class' => 'order-carrier',
                    ],
                ])
                ->add('warehouse', WarehouseChoiceType::class, [
                    'attr'      => [
                        'class' => 'order-warehouse',
                    ],
                ])
                // Supplier fields
                ->add('shippingCost', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.shipping_cost',
                    'base'   => $currency,
                ])
                ->add('discountTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.discount_total',
                    'base'   => $currency,
                ])
                ->add('taxTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.tax_total',
                    'base'   => $currency,
                    'disabled' => true,
                ])
                ->add('paymentTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.payment_total',
                    'base'   => $currency,
                    'disabled' => true,
                ])
                ->add('paymentDate', Type\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.payment_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                ])
                ->add('paymentDueDate', Type\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.payment_due_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                ])
                // Forwarder
                ->add('forwarderFee', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_fee',
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsTax', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.customs_tax',
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsVat', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.customs_vat',
                    'disabled' => !$hasCarrier,
                ])
                ->add('forwarderTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_total',
                    'disabled' => true,
                ])
                ->add('forwarderDate', Type\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                    'disabled' => !$hasCarrier,
                ])
                ->add('forwarderDueDate', Type\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_due_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                    'disabled' => !$hasCarrier,
                ])
                // Tracking
                ->add('trackingUrls', CollectionType::class, [
                    'label'         => 'ekyna_commerce.supplier_order.field.tracking_urls',
                    'entry_type'    => Type\UrlType::class,
                    'entry_options' => ['required' => true],
                    'required'      => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ])
                ->add('description', Type\TextareaType::class, [
                    'label'    => 'ekyna_commerce.field.description',
                    'required' => false,
                ]);
        });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-supplier-submit');
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', SupplierOrderSubmit::class);
    }
}
