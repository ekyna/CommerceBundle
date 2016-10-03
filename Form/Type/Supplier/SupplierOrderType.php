<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form;

/**
 * Class SupplierOrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Symfony\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'disabled' => true,
            ])
            ->add('currency', Commerce\CurrencyChoiceType::class, [
                'label' => 'ekyna_commerce.currency.label.singular',
            ])
            ->add('paymentDate', Symfony\DateTimeType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.payment_date',
//                'format' => 'dd/MM/yyyy',
                'required' => false,
            ])
            ->add('expectedDeliveryDate', Symfony\DateTimeType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.expected_delivery_date',
//                'format' => 'dd/MM/yyyy',
                'required' => false,
            ])
            ->add('items', SupplierOrderItemsType::class);

        $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) use ($options) {
            $form = $event->getForm();

            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
            $supplierOrder = $event->getData();

            /** @var \Ekyna\Component\Commerce\Common\Model\CurrencyInterface $currency */
            $currency = null !== $supplierOrder ? $supplierOrder->getCurrency() : null;

            $adminMode = $options['admin_mode'];
            $locked = (null !== $supplierOrder) && ($supplierOrder->getState() !== SupplierOrderStates::STATE_NEW);

            $form->add('paymentTotal', MoneyType::class, [
                'label'    => 'ekyna_core.field.amount',
                'currency' => $currency ? $currency->getCode() : 'EUR', // TODO default user currency
                'disabled' => $locked || !$adminMode,
            ]);
        });
    }
}
