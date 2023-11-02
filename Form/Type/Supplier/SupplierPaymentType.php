<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates as BStates;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Model\ConstantsInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as CStates;
use Ekyna\Component\Commerce\Supplier\Model\SupplierPaymentInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierPaymentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('state', ConstantChoiceType::class, [
                'label'       => t('field.status', [], 'EkynaUi'),
                'class'       => BStates::class,
                'filter'      => CStates::getSupplierPaymentStates(),
                'filter_mode' => ConstantsInterface::FILTER_RESTRICT,
            ])
            ->add('toForwarder', CheckboxType::class, [
                'label'    => t('supplier_payment.field.to_forwarder', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('exchangeDate', DateTimeType::class, [
                'label' => t('field.date', [], 'EkynaUi'),
            ])
            ->add('description', TextareaType::class, [
                'label'    => t('field.description', [], 'EkynaCommerce'),
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var SupplierPaymentInterface $order */
            $payment = $event->getData();

            /** @var CurrencyInterface $currency */
            if (null === $currency = $payment->getOrder()->getCurrency()) {
                throw new LogicException("Supplier order's currency must be set at this point.");
            }

            $event
                ->getForm()
                ->add('amount', MoneyType::class, [
                    'label'    => t('field.amount', [], 'EkynaUi'),
                    'decimal'  => true,
                    'currency' => $currency->getCode(),
                ]);
        });
    }
}
