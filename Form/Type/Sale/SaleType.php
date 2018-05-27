<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerSearchType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\IdentityType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\VatNumberType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\RelayPointType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodPickType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
            ->add('currency', CurrencyChoiceType::class)
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
            ->add('autoDiscount', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.sale.field.auto_discount',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('taxExempt', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.sale.field.tax_exempt',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('shipmentMethod', ShipmentMethodPickType::class, [
                'available' => !$options['admin_mode'],
            ])
            ->add('paymentTerm', PaymentTermChoiceType::class, [
                'required' => false,
            ])
            ->add('voucherNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'required' => false,
            ])
            ->add('originNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.origin_number',
                'required' => false,
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
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var SaleInterface $sale */
            $sale = $event->getData();
            $form = $event->getForm();

            if (null !== $currency = $sale->getCurrency()) {
                $currency = $currency->getCode();
            } else {
                $currency = $this->defaultCurrency;
            }

            $form
                ->add('number', Type\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'required' => false,
                    'disabled' => null !== $sale->getId(),
                ])
                ->add('depositTotal', MoneyType::class, [
                    'label'    => 'ekyna_commerce.sale.field.deposit_total',
                    'currency' => $currency,
                ])
                ->add('outstandingLimit', MoneyType::class, [
                    'label'    => 'ekyna_commerce.sale.field.outstanding_limit',
                    'currency' => $currency,
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
