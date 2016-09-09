<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormBuilder
{
    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param string $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * Build the product choice form.
     *
     * @param FormInterface $form
     */
    public function buildChoiceForm(FormInterface $form)
    {
        $form->add('subject', EntitySearchType::class, [
            'label'           => 'ekyna_commerce.product.label.singular',
            'class'           => $this->productClass,
            'search_route'    => 'ekyna_commerce_product_admin_search',
            'find_route'      => 'ekyna_commerce_product_admin_find',
            'allow_clear'     => false,
            'format_function' =>
                "if(!data.id)return 'Rechercher';" .
                "return $('<span>'+data.designation+'</span>');",
            'required'        => false,
        ]);
    }

    /**
     * Builds the configurable product form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     */
    public function buildConfigurableForm(FormInterface $form, ProductInterface $product)
    {
        ProductTypes::assertConfigurable($product);

        foreach ($product->getBundleSlots() as $bundleSlot) {

            $choices = [];
            foreach ($bundleSlot->getChoices() as $choice) {
                $choices[$choice->getProduct()->getDesignation()] = $choice->getId();
            }

            $prefix = 'subjectData[configuration][' . $bundleSlot->getId() . ']';

            $form
                ->add('choice_' . $bundleSlot->getId(), Type\ChoiceType::class, [
                    'label'    => $bundleSlot->getDescription(),
                    'choices'  => $choices,
                    'property_path' => $prefix . '[choice_id]',
                    'required' => true,
                ])
                ->add('quantity_' . $bundleSlot->getId(), Type\RangeType::class, [
                    'label'    => 'ekyna_core.field.quantity',
                    'property_path' => $prefix . '[quantity]',
                    'required' => true,
                    'attr' => [ // TODO Min and max are choice relative
                        'min' => 1,
                        'max' => 1,
                    ]
                ]);
        }
    }
}
