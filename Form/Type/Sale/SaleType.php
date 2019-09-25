<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MoneyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\RelayPointType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodPickType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $class
     * @param string $defaultCurrency
     */
    public function __construct(string $class, string $defaultCurrency)
    {
        parent::__construct($class);

        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', CustomerSearchType::class, [
                'required' => false,
            ])
            ->add('customerGroup', CustomerGroupChoiceType::class, [
                'required' => false,
            ])
            ->add('company', Type\TextType::class, [
                'label'    => 'ekyna_core.field.company',
                'required' => false,
                'attr'     => [
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('identity', IdentityType::class, [
                'required' => false,
                'section'  => 'sale',
            ])
            ->add('email', Type\EmailType::class, [
                'label'    => 'ekyna_core.field.email',
                'required' => false,
                'attr'     => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('invoiceAddress', SaleAddressType::class, [
                'label'          => 'ekyna_commerce.sale.field.invoice_address',
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'customer_field' => 'customer',
            ])
            ->add('deliveryAddress', SaleAddressType::class, [
                'label'          => 'ekyna_commerce.sale.field.delivery_address',
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'delivery'       => true,
                'customer_field' => 'customer',
            ])
            ->add('vatNumber', VatNumberType::class)
            ->add('vatValid', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.pricing.field.vat_valid',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('comment', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.comment',
                'required' => false,
            ])
            ->add('documentComment', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.sale.field.document_comment',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ])
            ->add('preparationNote', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.sale.field.preparation_note',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var SaleInterface $sale */
            $sale = $event->getData();
            $form = $event->getForm();

            $locked = $sale instanceof CartInterface;

            if (!$currencyLocked = $sale->hasPayments()) {
                if ($sale instanceof ShipmentSubjectInterface && $sale->hasShipments()) {
                    $currencyLocked = true;
                } elseif ($sale instanceof InvoiceSubjectInterface && $sale->hasInvoices()) {
                    $currencyLocked = true;
                }
            }

            $form
                ->add('currency', CurrencyChoiceType::class, [
                    'required' => !($locked || $currencyLocked),
                    'disabled' => $locked || $currencyLocked,
                ])
                ->add('locale', LocaleChoiceType::class, [
                    'required' => !($locked || $currencyLocked),
                    'disabled' => $locked || $currencyLocked,
                ])
                ->add('autoShipping', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.sale.field.auto_shipping',
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('autoDiscount', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.sale.field.auto_discount',
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('autoNotify', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.sale.field.auto_notify',
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('taxExempt', Type\CheckboxType::class, [
                    'label'    => 'ekyna_commerce.sale.field.tax_exempt',
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('shipmentMethod', ShipmentMethodPickType::class, [
                    'disabled'  => $locked,
                    'available' => !$options['admin_mode'],
                ])
                ->add('paymentMethod', PaymentMethodChoiceType::class, [
                    'disabled'    => $locked,
                    'required'    => false,
                    'enabled'     => !$options['admin_mode'],
                    'available'   => !$options['admin_mode'],
                    'private'     => !$options['admin_mode'],
                    'offline'     => true,
                    'credit'      => false,
                    'outstanding' => false,
                    'attr'        => [
                        'help_text' => 'ekyna_commerce.customer.help.default_payment_method',
                    ],
                ])
                ->add('paymentTerm', PaymentTermChoiceType::class, [
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('title', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.title',
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('voucherNumber', Type\TextType::class, [
                    'label'    => 'ekyna_commerce.sale.field.voucher_number',
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('originNumber', Type\TextType::class, [
                    'label'    => 'ekyna_commerce.sale.field.origin_number',
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'required' => false,
                    'disabled' => null !== $sale->getId(),
                ])
                ->add('depositTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.sale.field.deposit_total',
                    'subject'  => $sale,
                    'disabled' => $locked,
                ])
                ->add('outstandingLimit', MoneyType::class, [
                    'label'    => 'ekyna_commerce.sale.field.outstanding_limit',
                    'subject'  => $sale,
                    'disabled' => $locked,
                ])
                ->add('relayPoint', RelayPointType::class, [
                    'search' => $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress(),
                ]);
        });

        FormUtil::bindFormEventsToChildren(
            $builder,
            [
                FormEvents::PRE_SET_DATA => 2048,
                FormEvents::PRE_SUBMIT   => 2048,
                FormEvents::POST_SUBMIT  => 2048,
            ],
            ['invoiceAddress', 'deliveryAddress']
        );
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-sale');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['address_type'])
            ->setAllowedTypes('address_type', 'string'); // TODO validation
    }
}
