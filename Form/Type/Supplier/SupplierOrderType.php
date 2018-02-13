<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates as BStates;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates as CStates;
use Ekyna\Component\Commerce\Exception\LogicException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form;

/**
 * Class SupplierOrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $supplierClass;

    /**
     * @var string
     */
    protected $supplierProductClass;

    /**
     * @var string
     */
    protected $carrierClass;

    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $supplierClass
     * @param string $supplierProductClass
     * @param string $carrierClass
     * @param string $defaultCurrency
     */
    public function __construct($dataClass, $supplierClass, $supplierProductClass, $carrierClass, $defaultCurrency)
    {
        parent::__construct($dataClass);

        $this->supplierClass = $supplierClass;
        $this->supplierProductClass = $supplierProductClass;
        $this->carrierClass = $carrierClass;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $order */
            $order = $event->getData();

            // Step 1: Supplier is not selected
            if (null === $supplier = $order->getSupplier()) {
                $form
                    ->add('supplier', ResourceType::class, [
                        'label' => 'ekyna_commerce.supplier.label.singular',
                        'class' => $this->supplierClass,
                    ]);

                return;
            }

            /** @var \Ekyna\Component\Commerce\Common\Model\CurrencyInterface $currency */
            if (null === $currency = $order->getCurrency()) {
                throw new LogicException("Supplier order's currency must be set at this point.");
            }

            $requiredEda = $order->getState() !== CStates::STATE_NEW;
            $hasCarrier = null !== $order->getCarrier();

            // Step 2: Supplier is selected
            $form
                ->add('supplier', ResourceType::class, [
                    'label'    => 'ekyna_commerce.supplier.label.singular',
                    'class'    => $this->supplierClass,
                    'disabled' => true,
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
                ->add('number', Symfony\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('currency', Commerce\Common\CurrencyChoiceType::class, [
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('state', Symfony\ChoiceType::class, [
                    'label'    => 'ekyna_core.field.status',
                    'choices'  => BStates::getChoices(),
                    'required' => false,
                    'disabled' => true,
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
                ->add('paymentDate', Symfony\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.payment_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                ])
                ->add('paymentDueDate', Symfony\DateType::class, [
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
                ->add('forwarderDate', Symfony\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                    'disabled' => !$hasCarrier,
                ])
                ->add('forwarderDueDate', Symfony\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_due_date',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => false,
                    'disabled' => !$hasCarrier,
                ])
                // EDA / Tracking
                ->add('estimatedDateOfArrival', Symfony\DateType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.estimated_date_of_arrival',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => $requiredEda,
                ])
                ->add('trackingUrls', CollectionType::class, [
                    'label'         => 'ekyna_commerce.supplier_order.field.tracking_urls',
                    'entry_type'    => Symfony\UrlType::class,
                    'entry_options' => ['required' => true],
                    'required'      => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                ]);

            /* ----------- Supplier order compose ----------- */

            /**
             * @param EntityRepository $repository
             *
             * @return \Doctrine\ORM\QueryBuilder
             */
            $queryBuilder = function (EntityRepository $repository) use ($supplier) {
                $qb = $repository->createQueryBuilder('sp');

                return $qb
                    ->andWhere($qb->expr()->eq('sp.supplier', ':supplier'))
                    ->setParameter('supplier', $supplier);
            };

            $formatter = \NumberFormatter::create(\Locale::getDefault(), \NumberFormatter::CURRENCY);

            /**
             * @param \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $value
             *
             * @return string
             */
            $choiceLabel = function ($value) use ($formatter) {
                return sprintf(
                    '[%s] %s - %s (%s) ',
                    $value->getReference(),
                    $value->getDesignation(),
                    $formatter->formatCurrency($value->getNetPrice(), $value->getSupplier()->getCurrency()->getCode()),
                    round($value->getAvailableStock())
                );
            };

            /**
             * @param \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $value
             *
             * @return array
             */
            $choiceAttributes = function ($value) {
                return [
                    'data-designation' => $value->getDesignation(),
                    'data-reference'   => $value->getReference(),
                    'data-net-price'   => $value->getNetPrice(),
                ];
            };

            $form
                ->add('items', SupplierOrderItemsType::class, [
                    'currency' => $supplier->getCurrency()->getCode(),
                    'attr'     => [
                        'class' => 'order-compose-items',
                    ],
                ])
                ->add('quickAddSelect', EntityType::class, [
                    'label'         => 'ekyna_commerce.supplier_product.label.singular',
                    'class'         => $this->supplierProductClass,
                    'query_builder' => $queryBuilder,
                    'choice_label'  => $choiceLabel,
                    'choice_attr'   => $choiceAttributes,
                    'placeholder'   => false,
                    'required'      => false,
                    'mapped'        => false,
                    'attr'          => [
                        'class' => 'order-compose-quick-add-select',
                    ],
                ])
                ->add('quickAddButton', Symfony\ButtonType::class, [
                    'label' => 'ekyna_core.button.add',
                    'attr'  => [
                        'class' => 'order-compose-quick-add-button',
                    ],
                ]);
        });
    }

    /**
     * @inheritDoc
     */
    public function finishView(Form\FormView $view, Form\FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-supplier-order');
    }
}
