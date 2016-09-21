<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleSlotType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotType extends ResourceFormType
{
    /**
     * @var string
     */
    private $bundleChoiceClass;


    /**
     * Constructor.
     *
     * @param string $bundleSlotClass
     * @param string $bundleChoiceClass
     */
    public function __construct($bundleSlotClass, $bundleChoiceClass)
    {
        parent::__construct($bundleSlotClass);

        $this->bundleChoiceClass = $bundleChoiceClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['configurable']) {
            // Bundle type : ensure one and only one choice.
            $bundleSlotClass = $this->dataClass;
            $bundleChoiceClass = $this->bundleChoiceClass;

            $builder
                ->addModelTransformer(new CallbackTransformer(
                    function ($slot) use ($bundleSlotClass, $bundleChoiceClass) {
                        if (null === $slot) {
                            /** @var \Ekyna\Component\Commerce\Product\Model\BundleSlotInterface $slot */
                            $slot = new $bundleSlotClass();
                        }

                        $choices = $slot->getChoices();
                        if ($choices->isEmpty()) {
                            $slot->addChoice(new $bundleChoiceClass);
                        }

                        return $slot;
                    },
                    function ($data) {
                        return $data;
                    }
                ));
        } else {
            $builder->add('translations', TranslationsFormsType::class, [
                'form_type'      => BundleSlotTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ]);
        }

        $builder
            ->add('choices', BundleChoicesType::class, [
                'configurable' => $options['configurable'],
                'choice_class' => $this->bundleChoiceClass,
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['configurable'] = $options['configurable'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'configurable' => false,
            ])
            ->setAllowedTypes('configurable', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_bundle_slot';
    }
}
