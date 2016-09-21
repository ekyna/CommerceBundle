<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $productClass;


    /**
     * Constructor.
     *
     * @param string $bundleChoiceClass
     * @param string $productClass
     */
    public function __construct($bundleChoiceClass, $productClass)
    {
        parent::__construct($bundleChoiceClass);

        $this->productClass = $productClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', ResourceType::class, [
                'label'         => 'ekyna_commerce.product.label.singular',
                'sizing'        => 'sm',
//                'allow_new'     => true,
                'class'         => $this->productClass,
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('p');

                    return $qb->andWhere($qb->expr()->in('p.type', [
                        ProductTypes::TYPE_SIMPLE,
                        ProductTypes::TYPE_VARIANT,
                    ]));
                },
            ]);

        if ($options['configurable']) {
            // TODO options ( + fixed/user defined)
            $builder
                ->add('rules', CollectionType::class, [
                    'label'           => 'ekyna_commerce.bundle_choice_rule.label.plural',
                    'sub_widget_col'  => 9,
                    'button_col'      => 3,
                    'allow_sort'      => true,
                    'add_button_text' => 'ekyna_commerce.bundle_choice_rule.button.add',
                    'entry_type'      => BundleChoiceRuleType::class,
                ])
                ->add('minQuantity', Type\NumberType::class, [
                    'label'  => 'ekyna_commerce.bundle_choice.field.min_quantity',
                    'sizing' => 'sm',
                ])
                ->add('maxQuantity', Type\NumberType::class, [
                    'label'  => 'ekyna_commerce.bundle_choice.field.min_quantity',
                    'sizing' => 'sm',
                ])
                ->add('position', Type\HiddenType::class, [
                    'attr' => [
                        'data-collection-role' => 'position',
                    ],
                ]);
        } else {
            $builder
                ->add('quantity', Type\NumberType::class, [
                    'label'         => 'ekyna_core.field.quantity',
                    'property_path' => 'minQuantity',
                ]);
        }
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
        return 'ekyna_commerce_bundle_choice';
    }
}
