<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SaleTransformType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ])
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => $options['message'],
                'attr'        => ['align_with_widget' => true],
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new Assert\IsTrue(),
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var SaleInterface $sale */
            $sale = $event->getData();
            $form = $event->getForm();

            $customer = $sale->getCustomer();
            if (null !== $customer) {
                if ($customer->hasParent()) {
                    $choices = [$customer, $customer->getParent()];
                    $sale->setCustomer($customer->getParent());

                } elseif ($customer->hasChildren()) {
                    $choices = $customer->getChildren()->toArray();
                    array_unshift($choices, $customer);
                } else {
                    return;
                }

                $form->add('customer', Type\ChoiceType::class, [
                    'label'             => 'ekyna_commerce.customer.label.singular',
                    'choices'           => $choices,
                    'choice_value'      => 'id',
                    'choice_label'      => function ($customer) {
                        return (string)$customer;
                    },
                    'attr' => [
                        'help_text' => 'ekyna_commerce.customer.help.hierarchy',
                    ],
                    'select2' => false,
                ]);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => SaleInterface::class,
                'message'    => null,
            ])
            ->setAllowedTypes('message', 'string');
    }
}
