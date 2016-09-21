<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\ProductToBundleSlotChoiceTransformer;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Product\Model\BundleChoiceInterface;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurableSlotType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableSlotType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        $subjectField = $builder
            ->create('subject', Type\ChoiceType::class, [
                'label'        => $bundleSlot->getDescription(),
                'choices'      => $bundleSlot->getChoices(),
                'choice_value' => 'id',
                'choice_label' => 'product.designation',
                'choice_attr'  => function (BundleChoiceInterface $choice) {
                    return [
                        'data-config' => json_encode($this->buildChoiceAttributes($choice)),
                    ];
                },
                'expanded'     => true,
            ])
            ->addModelTransformer(new ProductToBundleSlotChoiceTransformer($bundleSlot));

        $builder
            ->add($subjectField)
            ->add('quantity', Type\IntegerType::class, [
                'label' => 'ekyna_core.field.quantity',
                'attr'  => [
                    'min' => 1,
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function buildChoiceAttributes(BundleChoiceInterface $choice)
    {
        $product = $choice->getProduct();

        return [
            'min_quantity' => $choice->getMinQuantity(),
            'max_quantity' => $choice->getMaxQuantity(),
            'title'        => $product->getTitle(),
            'description'  => $product->getDescription(),
            'image'        => '/bundles/ekynacommerce/img/no-image.jpg', // TODO remove as not managed by this bundle
            'price'        => $product->getNetPrice(), // TODO
        ];
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        $view->vars['slot_title'] = $bundleSlot->getTitle();
        $view->vars['slot_description'] = $bundleSlot->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('label', false)
            ->setDefault('data_class', SaleItemInterface::class)
            ->setRequired(['bundle_slot'])
            ->setAllowedTypes('bundle_slot', BundleSlotInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_configurable_slot';
    }
}
