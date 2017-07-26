<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $supplierClass;


    /**
     * Constructor.
     *
     * @param string $supplierClass
     */
    public function __construct($supplierClass)
    {
        $this->supplierClass = $supplierClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_commerce.supplier.label.singular',
            'class' => $this->supplierClass,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}