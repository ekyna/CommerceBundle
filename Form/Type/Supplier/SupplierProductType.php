<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type as Commerce;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\Type as Symfony;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SupplierProductType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('reference', Symfony\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ])
            ->add('weight', Symfony\NumberType::class, [
                'label' => 'ekyna_core.field.weight',
                'scale' => 2,
                'attr'  => [
                    'input_group' => ['append' => 'Kg'],
                ],
            ])
            ->add('availableStock', Symfony\NumberType::class, [
                'label' => 'ekyna_commerce.supplier_product.field.available_stock',
                'scale' => 3,
            ])
            ->add('orderedStock', Symfony\NumberType::class, [
                'label' => 'ekyna_commerce.supplier_product.field.ordered_stock',
                'scale' => 3,
            ])
            ->add('estimatedDateOfArrival', Symfony\DateTimeType::class, [
                'label'    => 'ekyna_commerce.supplier_product.field.estimated_date_of_arrival',
                'required' => false,
            ])
            ->add('subjectIdentity', Commerce\Subject\SubjectChoiceType::class, [
                'label'     => 'ekyna_commerce.supplier_product.field.subject',
                'lock_mode' => true,
                'required'  => false,
                'context'   => SubjectProviderInterface::CONTEXT_SUPPLIER,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $data */
            $data = $event->getData();

            if (null === $data) {
                throw new LogicException("Supplier product must be set at this point.");
            }
            if (null === $supplier = $data->getSupplier()) {
                throw new LogicException("Supplier product's supplier must be set at this point.");
            }
            if (null === $currency = $supplier->getCurrency()) {
                throw new LogicException("Supplier's currency must be set at this point.");
            }

            $form = $event->getForm();

            $form->add('netPrice', MoneyType::class, [
                'label'    => 'ekyna_core.field.price',
                'currency' => $currency->getCode(),
            ]);
        });
    }
}
