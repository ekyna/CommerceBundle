<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates as BStates;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates as CStates;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;

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
    protected $supplierProductClass;


    /**
     * Constructor.
     *
     * @param string $orderClass
     * @param string $productClass
     */
    public function __construct(string $orderClass, string $productClass)
    {
        parent::__construct($orderClass);

        $this->supplierProductClass = $productClass;
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
                $form->add('supplier', SupplierChoiceType::class);

                return;
            }

            /** @var \Ekyna\Component\Commerce\Common\Model\CurrencyInterface $currency */
            if (null === $currency = $order->getCurrency()) {
                throw new LogicException("Supplier order's currency must be set at this point.");
            }

            $hasCarrier = null !== $order->getCarrier();

            // Step 2: Supplier is selected
            $form
                ->add('supplier', SupplierChoiceType::class, [
                    'disabled' => true,
                    'attr'     => [
                        'class' => 'order-supplier',
                    ],
                ])
                ->add('carrier', SupplierCarrierChoiceType::class, [
                    'required'  => false,
                    'allow_new' => true,
                    'attr'      => [
                        'class' => 'order-carrier',
                    ],
                ])
                ->add('warehouse', Commerce\Stock\WarehouseChoiceType::class, [
                    'disabled' => true, // TODO Temporary
                    'attr' => [
                        'class' => 'order-warehouse',
                    ],
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('currency', Commerce\Common\CurrencyChoiceType::class, [
                    'disabled' => true,
                ])
                ->add('state', Type\ChoiceType::class, [
                    'label'    => 'ekyna_core.field.status',
                    'choices'  => BStates::getChoices(),
                    'disabled' => true,
                ])
                // Supplier fields
                ->add('shippingCost', Commerce\Common\MoneyType::class, [
                    'label'      => 'ekyna_commerce.supplier_order.field.shipping_cost',
                    'base'   => $currency,
                ])
                ->add('discountTotal', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.discount_total',
                    'base' => $currency,
                ])
                ->add('taxTotal', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.tax_total',
                    'base' => $currency,
                    'disabled' => true,
                ])
                ->add('paymentTotal', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.payment_total',
                    'base' => $currency,
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
                ->add('forwarderFee', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_fee',
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsTax', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.customs_tax',
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsVat', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.customs_vat',
                    'disabled' => !$hasCarrier,
                ])
                ->add('forwarderTotal', Commerce\Common\MoneyType::class, [
                    'label'    => 'ekyna_commerce.supplier_order.field.forwarder_total',
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
                // EDA / Tracking
                ->add('estimatedDateOfArrival', Type\DateType::class, [
                    'label'    => 'ekyna_commerce.field.estimated_date_of_arrival',
                    'format'   => 'dd/MM/yyyy', // TODO localised configurable format
                    'required' => CStates::isStockableState($order),
                ])
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

            $this->buildComposeForm($form, $supplier);
        });
    }

    private function buildComposeForm(Form\FormInterface $form, SupplierInterface $supplier): void
    {
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
                'data-weight'      => $value->getWeight(),
                'data-tax-group'   => $value->getTaxGroup()->getId(),
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
            ->add('quickAddButton', Type\ButtonType::class, [
                'label' => 'ekyna_core.button.add',
                'attr'  => [
                    'class' => 'order-compose-quick-add-button',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(Form\FormView $view, Form\FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-supplier-order');
    }
}
