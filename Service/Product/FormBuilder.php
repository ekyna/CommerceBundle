<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Bundle\CommerceBundle\Form\Type\ConfigurableSlotsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\ConfigurableSlotType;
use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
     * Build the product item form.
     *
     * @param FormInterface     $form
     * @param SaleItemInterface $item
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item)
    {
        $form->add('quantity', Type\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr' => [
                'min' => 1,
            ]
        ]);

        /** @var ProductInterface $product */
        $product = $item->getSubject();

        if ($product->getType() === ProductTypes::TYPE_CONFIGURABLE) {
            $form->add('configuration', ConfigurableSlotsType::class, [
                'bundle_slots' => $product->getBundleSlots()->toArray(),
                'item' => $item,
            ]);
        }
    }
}
