<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
     * @var string
     */
    private $carrierClass;

    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $orderClass
     * @param string $carrierClass
     * @param string $defaultCurrency
     */
    public function __construct($orderClass, $carrierClass, $defaultCurrency)
    {
        $this->orderClass = $orderClass;
        $this->carrierClass = $carrierClass;
        $this->defaultCurrency = $defaultCurrency;
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
            ->add('message', TinymceType::class, [
                'label'    => 'ekyna_core.field.message',
                'required' => false,
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
                ->get('order')
                ->add('estimatedDateOfArrival', Type\DateTimeType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.estimated_date_of_arrival',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => true,
                ])
                ->add('carrier', ResourceType::class, [
                    'label'     => 'ekyna_commerce.supplier_carrier.label.singular',
                    'class'     => $this->carrierClass,
                    'required'  => false,
                    'allow_new' => true,
                    'attr'      => [
                        'class' => 'order-carrier',
                    ],
                ])
                // Supplier fields
                ->add('shippingCost', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.shipping_cost',
                    'currency' => $currency->getCode(),
                ])
                ->add('discountTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.discount_total',
                    'currency' => $currency->getCode(),
                ])
                ->add('taxTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.tax_total',
                    'currency' => $currency->getCode(),
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('paymentTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.payment_total',
                    'currency' => $currency->getCode(),
                    'disabled' => true,
                    'required' => false,
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
                    'currency' => $this->defaultCurrency,
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsTax', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.customs_tax',
                    'currency' => $this->defaultCurrency,
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsVat', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.customs_vat',
                    'currency' => $this->defaultCurrency,
                    'disabled' => !$hasCarrier,
                ])
                ->add('forwarderTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_total',
                    'currency' => $this->defaultCurrency,
                    'disabled' => true,
                    'required' => false,
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
