<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates as BStates;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates as CStates;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form;
use Symfony\Component\Form\Extension\Core\Type;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierOrderType
 *
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderType extends AbstractResourceType
{
    public function __construct(
        protected readonly FormatterFactory $formatterFactory,
        protected readonly string           $supplierProductClass
    ) {
    }

    public function buildForm(Form\FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            /** @var SupplierOrderInterface $order */
            $order = $event->getData();

            // Step 1: Supplier is not selected
            if (null === $supplier = $order->getSupplier()) {
                $form->add('supplier', SupplierChoiceType::class);

                return;
            }

            /** @var CurrencyInterface $currency */
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
                ->add('carrier', ResourceChoiceType::class, [
                    'resource'  => 'ekyna_commerce.supplier_carrier',
                    'required'  => false,
                    'allow_new' => true,
                    'attr'      => [
                        'class' => 'order-carrier',
                    ],
                ])
                ->add('warehouse', Commerce\Stock\WarehouseChoiceType::class, [
                    'disabled' => true, // TODO Temporary
                    'attr'     => [
                        'class' => 'order-warehouse',
                    ],
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => t('field.number', [], 'EkynaUi'),
                    'required' => false,
                    'disabled' => true,
                ])
                ->add('currency', Commerce\Common\CurrencyChoiceType::class, [
                    'disabled' => true,
                ])
                ->add('state', ConstantChoiceType::class, [
                    'label'    => t('field.status', [], 'EkynaUi'),
                    'class'    => BStates::class,
                    'disabled' => true,
                ])
                // Supplier fields
                ->add('shippingCost', Commerce\Common\MoneyType::class, [
                    'label' => t('supplier_order.field.shipping_cost', [], 'EkynaCommerce'),
                    'base'  => $currency,
                ])
                ->add('discountTotal', Commerce\Common\MoneyType::class, [
                    'label' => t('supplier_order.field.discount_total', [], 'EkynaCommerce'),
                    'base'  => $currency,
                ])
                ->add('taxTotal', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.tax_total', [], 'EkynaCommerce'),
                    'base'     => $currency,
                    'disabled' => true,
                ])
                ->add('paymentTotal', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.payment_total', [], 'EkynaCommerce'),
                    'base'     => $currency,
                    'disabled' => true,
                ])
                ->add('paymentPaidTotal', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.payment_paid_total', [], 'EkynaCommerce'),
                    'base'     => $currency,
                    'disabled' => true,
                ])
                ->add('paymentDueDate', Type\DateType::class, [
                    'label'    => t('supplier_order.field.payment_due_date', [], 'EkynaCommerce'),
                    'required' => false,
                ])
                // Forwarder
                ->add('forwarderFee', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.forwarder_fee', [], 'EkynaCommerce'),
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsTax', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.customs_tax', [], 'EkynaCommerce'),
                    'disabled' => !$hasCarrier,
                ])
                ->add('customsVat', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.customs_vat', [], 'EkynaCommerce'),
                    'disabled' => !$hasCarrier,
                ])
                ->add('forwarderTotal', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.forwarder_total', [], 'EkynaCommerce'),
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('forwarderPaidTotal', Commerce\Common\MoneyType::class, [
                    'label'    => t('supplier_order.field.forwarder_paid_total', [], 'EkynaCommerce'),
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('forwarderDueDate', Type\DateType::class, [
                    'label'    => t('supplier_order.field.forwarder_due_date', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => !$hasCarrier,
                ])
                // EDA / Tracking
                ->add('estimatedDateOfArrival', Type\DateType::class, [
                    'label'    => t('field.estimated_date_of_arrival', [], 'EkynaCommerce'),
                    'required' => CStates::isStockableState($order),
                ])
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

            $this->buildComposeForm($form, $supplier);
        });
    }

    private function buildComposeForm(Form\FormInterface $form, SupplierInterface $supplier): void
    {
        $queryBuilder = function (EntityRepository $repository) use ($supplier): QueryBuilder {
            $qb = $repository->createQueryBuilder('sp');

            return $qb
                ->andWhere($qb->expr()->eq('sp.supplier', ':supplier'))
                ->setParameter('supplier', $supplier);
        };

        $formatter = $this->formatterFactory->create(null, $supplier->getCurrency()->getCode());

        $choiceLabel = function (SupplierProductInterface $value) use ($formatter): string {
            return sprintf(
                '[%s] %s - %s (%s) ',
                $value->getReference(),
                $value->getDesignation(),
                $formatter->currency($value->getNetPrice(), null, 5),
                $value->getAvailableStock()->toFixed()
            );
        };

        $choiceAttributes = function (SupplierProductInterface $value): array {
            $unit = $value->getUnit();

            return [
                'data-designation' => $value->getDesignation(),
                'data-reference'   => $value->getReference(),
                'data-net-price'   => $value->getNetPrice()->toFixed(5),
                'data-weight'      => $value->getWeight()->toFixed(3),
                'data-packing'     => Units::round($value->getPacking(), $unit),
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
                'label'         => t('supplier_product.label.singular', [], 'EkynaCommerce'),
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
                'label' => t('button.add', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'order-compose-quick-add-button',
                ],
            ]);
    }

    public function finishView(Form\FormView $view, Form\FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-supplier-order');
    }
}
