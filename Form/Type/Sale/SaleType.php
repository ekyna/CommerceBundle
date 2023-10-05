<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

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
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\LocaleChoiceType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
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

use function Symfony\Component\Translation\t;

/**
 * Class SaleType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleType extends AbstractResourceType
{
    protected string $defaultCurrency;

    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', CustomerSearchType::class, [
                'required' => false,
            ])
            ->add('customerGroup', CustomerGroupChoiceType::class, [
                'required' => false,
            ])
            ->add('company', Type\TextType::class, [
                'label'    => t('field.company', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'maxlength'    => 35,
                    'autocomplete' => 'organization',
                ],
            ])
            ->add('companyNumber', Type\TextType::class, [
                'label'    => t('customer.field.company_number', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('identity', IdentityType::class, [
                'required' => false,
                'section'  => 'sale',
            ])
            ->add('email', Type\EmailType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('invoiceAddress', SaleAddressType::class, [
                'label'          => t('sale.field.invoice_address', [], 'EkynaCommerce'),
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'customer_field' => 'customer',
            ])
            ->add('deliveryAddress', SaleAddressType::class, [
                'label'          => t('sale.field.delivery_address', [], 'EkynaCommerce'),
                'address_type'   => $options['address_type'],
                'inherit_data'   => true,
                'delivery'       => true,
                'customer_field' => 'customer',
            ])
            ->add('vatNumber', VatNumberType::class)
            ->add('vatValid', Type\CheckboxType::class, [
                'label'    => t('pricing.field.vat_valid', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('comment', Type\TextareaType::class, [
                'label'    => t('field.comment', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('documentComment', Type\TextareaType::class, [
                'label'    => t('sale.field.document_comment', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => t('field.description', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('preparationNote', Type\TextareaType::class, [
                'label'    => t('sale.field.preparation_note', [], 'EkynaCommerce'),
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
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
                    'label'    => t('sale.field.auto_shipping', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('autoDiscount', Type\CheckboxType::class, [
                    'label'    => t('sale.field.auto_discount', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('autoNotify', Type\CheckboxType::class, [
                    'label'    => t('sale.field.auto_notify', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('taxExempt', Type\CheckboxType::class, [
                    'label'    => t('sale.field.tax_exempt', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $locked,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('shipmentMethod', ShipmentMethodPickType::class, [
                    'subject'   => $sale,
                    'disabled'  => $locked,
                    'available' => !$options['admin_mode'],
                ])
                ->add('paymentMethod', PaymentMethodChoiceType::class, [
                    'disabled'    => $locked,
                    'required'    => false,
                    'enabled'     => !$options['admin_mode'],
                    'available'   => !$options['admin_mode'],
                    'public'      => !$options['admin_mode'],
                    'offline'     => true,
                    'credit'      => false,
                    'outstanding' => false,
                    'help'        => t('customer.help.default_payment_method', [], 'EkynaCommerce'),
                ])
                ->add('paymentTerm', PaymentTermChoiceType::class, [
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('title', Type\TextType::class, [
                    'label'    => t('field.title', [], 'EkynaUi'),
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('voucherNumber', Type\TextType::class, [
                    'label'    => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('originNumber', Type\TextType::class, [
                    'label'    => t('sale.field.origin_number', [], 'EkynaCommerce'),
                    'required' => false,
                    'disabled' => $locked,
                ])
                ->add('number', Type\TextType::class, [
                    'label'    => t('field.number', [], 'EkynaUi'),
                    'required' => false,
                    'disabled' => null !== $sale->getId(),
                ])
                ->add('depositTotal', MoneyType::class, [
                    'label'    => t('sale.field.deposit_total', [], 'EkynaCommerce'),
                    'subject'  => $sale,
                    'disabled' => $locked,
                ])
                ->add('outstandingLimit', MoneyType::class, [
                    'label'    => t('sale.field.outstanding_limit', [], 'EkynaCommerce'),
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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-sale');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['address_type'])
            ->setAllowedTypes('address_type', 'string'); // TODO validation
    }
}
