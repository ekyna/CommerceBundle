<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

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
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
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

        $fakeDesc =
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. ' .
            'Vestibulum sodales ornare sapien, vel ultrices sapien rhoncus eu.';

        $subjectField = $builder
            ->create('subject', Type\ChoiceType::class, [
                'label'        => $bundleSlot->getDescription(),
                'choices'      => $bundleSlot->getChoices(),
                'choice_value' => 'id',
                'choice_label' => 'product.designation',
                'choice_attr'  => function (BundleChoiceInterface $choice) use ($fakeDesc) {
                    $product = $choice->getProduct();

                    return [
                        'data-config' => json_encode([
                            'min_quantity' => $choice->getMinQuantity(),
                            'max_quantity' => $choice->getMaxQuantity(),
                            'title'        => $product->getDesignation(),
                            'description'  => $fakeDesc, // TODO
                            'image'        => 'bundles/app/img/no-image.jpg', // TODO
                            'price'        => 49.99,
                        ]),
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
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var BundleSlotInterface $bundleSlot */
        $bundleSlot = $options['bundle_slot'];

        $view->vars['slot_title'] = 'Emplacement ' . $bundleSlot->getId();
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
